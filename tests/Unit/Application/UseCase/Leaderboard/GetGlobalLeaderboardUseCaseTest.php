<?php
namespace Tests\Unit\Application\UseCase\Leaderboard;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Application\Service\CacheService;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Entity\Player;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class GetGlobalLeaderboardUseCaseTest extends TestCase {
    private GetGlobalLeaderboardUseCase $useCase;
    private $playerRepositoryMock;
    private $cacheServiceMock;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->cacheServiceMock = $this->createMock(CacheService::class);

        $this->useCase = new GetGlobalLeaderboardUseCase(
            $this->playerRepositoryMock,
            $this->cacheServiceMock
        );
    }

    public function testGetGlobalLeaderboardSuccessfully(): void {
        // Arrange
        $player1 = new Player(
            id: new PlayerId('player1'),
            nickname: 'PlayerOne',
            totalTrophies: new Trophy(3000),
            region: 'US'
        );

        $player2 = new Player(
            id: new PlayerId('player2'),
            nickname: 'PlayerTwo',
            totalTrophies: new Trophy(2500),
            region: 'US'
        );

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findTopByTrophies')
            ->with(10, 0)
            ->willReturn([$player1, $player2]);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(2);

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('set');

        // Act
        $result = $this->useCase->execute(10, 0);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(2, $result['entries']);
        
        $firstPlayer = $result['entries'][0];
        $this->assertEquals('player1', $firstPlayer['playerId']);
        $this->assertEquals('PlayerOne', $firstPlayer['nickname']);
        $this->assertEquals(3000, $firstPlayer['totalTrophies']);
        $this->assertEquals(1, $firstPlayer['rank']);
        $this->assertEquals(4, $firstPlayer['level']);

        $secondPlayer = $result['entries'][1];
        $this->assertEquals('player2', $secondPlayer['playerId']);
        $this->assertEquals('PlayerTwo', $secondPlayer['nickname']);
        $this->assertEquals(2500, $secondPlayer['totalTrophies']);
        $this->assertEquals(2, $secondPlayer['rank']);
        $this->assertEquals(3, $secondPlayer['level']);
    }

    public function testGetGlobalLeaderboardWithEmptyResult(): void {
        // Arrange
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findTopByTrophies')
            ->with(10, 0)
            ->willReturn([]);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(0);

        // Act
        $result = $this->useCase->execute(10, 0);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(0, $result['entries']);
        $this->assertEquals(0, $result['total']);
    }

    public function testGetGlobalLeaderboardWithCachedResult(): void {
        // Arrange
        $cachedData = json_encode([
            'entries' => [
                ['playerId' => 'player1', 'nickname' => 'CachedPlayer', 'totalTrophies' => 5000, 'rank' => 1, 'level' => 5]
            ],
            'total' => 1,
            'hasMore' => false,
            'page' => 1
        ]);

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($cachedData);

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('findTopByTrophies');

        // Act
        $result = $this->useCase->execute(10, 0);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result['entries']);
        $this->assertEquals('CachedPlayer', $result['entries'][0]['nickname']);
    }

    public function testGetGlobalLeaderboardWithPagination(): void {
        // Arrange
        $player = new Player(
            id: new PlayerId('player1'),
            nickname: 'PlayerOne',
            totalTrophies: new Trophy(2000),
            region: 'US'
        );

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findTopByTrophies')
            ->with(5, 10)
            ->willReturn([$player]);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(100);

        // Act
        $result = $this->useCase->execute(5, 10);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result['entries']);
        $this->assertEquals(11, $result['entries'][0]['rank']); // offset + index + 1
        $this->assertTrue($result['hasMore']);
        $this->assertEquals(3, $result['page']); // (10 / 5) + 1
    }

    public function testGetGlobalLeaderboardLimitsCappedAt500(): void {
        // Arrange
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findTopByTrophies')
            ->with(500, 0) // Should be capped at 500
            ->willReturn([]);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(0);

        // Act
        $result = $this->useCase->execute(1000, 0); // Request 1000, should be capped

        // Assert
        $this->assertIsArray($result);
    }

    public function testGetGlobalLeaderboardWithDifferentTrophyLevels(): void {
        // Arrange
        $players = [];
        $expectedLevels = [1, 2, 3, 4];
        $expectedTrophies = [500, 1500, 2500, 4000];

        for ($i = 0; $i < 4; $i++) {
            $players[] = new Player(
                id: new PlayerId("player{$i}"),
                nickname: "Player{$i}",
                totalTrophies: new Trophy($expectedTrophies[$i]),
                region: 'US'
            );
        }

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findTopByTrophies')
            ->willReturn($players);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(4);

        // Act
        $result = $this->useCase->execute(10, 0);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(4, $result['entries']);

        for ($i = 0; $i < 4; $i++) {
            $playerData = $result['entries'][$i];
            $this->assertEquals($expectedLevels[$i], $playerData['level']);
            $this->assertEquals($expectedTrophies[$i], $playerData['totalTrophies']);
        }
    }
}
