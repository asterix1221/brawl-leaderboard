<?php
namespace Tests\Unit\Application\UseCase\Leaderboard;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Application\Service\CacheService;
use App\Domain\Entity\Player;
use App\Domain\Entity\Score;
use App\Domain\Entity\Season;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\ScoreRepositoryInterface;
use App\Domain\Repository\SeasonRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\ValueObject\Uuid;

class GetGlobalLeaderboardUseCaseTest extends TestCase {
    private GetGlobalLeaderboardUseCase $useCase;
    private Season $activeSeason;
    private $playerRepositoryMock;
    private $scoreRepositoryMock;
    private $seasonRepositoryMock;
    private $cacheServiceMock;
    private Uuid $seasonId;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->scoreRepositoryMock = $this->createMock(ScoreRepositoryInterface::class);
        $this->seasonRepositoryMock = $this->createMock(SeasonRepositoryInterface::class);
        $this->cacheServiceMock = $this->createMock(CacheService::class);
        $this->activeSeason = $this->createActiveSeason();

        $this->seasonRepositoryMock
            ->method('findActive')
            ->willReturn($this->activeSeason);

        $this->seasonId = new Uuid('11111111-1111-1111-1111-111111111111');
        $activeSeason = new Season(
            $this->seasonId,
            'Season 1',
            new \DateTime('-1 day'),
            new \DateTime('+1 day'),
            true
        );

        $this->seasonRepositoryMock
            ->method('findActive')
            ->willReturn($activeSeason);

        $this->useCase = new GetGlobalLeaderboardUseCase(
            $this->playerRepositoryMock,
            $this->scoreRepositoryMock,
            $this->seasonRepositoryMock,
            $this->cacheServiceMock
        );
    }

    public function testGetGlobalLeaderboardSuccessfully(): void {
        // Arrange
        $player1 = new Player(new PlayerId('player1'), 'PlayerOne', new Trophy(3000), 'US');
        $player2 = new Player(new PlayerId('player2'), 'PlayerTwo', new Trophy(2500), 'US');

        $score1 = new Score(new Uuid('21111111-1111-1111-1111-111111111111'), new PlayerId('player1'), $this->seasonId, 5000);
        $score2 = new Score(new Uuid('21111111-1111-1111-1111-111111111112'), new PlayerId('player2'), $this->seasonId, 4000);

        $seasonId = $this->activeSeason->getId();
        $scores = [
            new Score(new Uuid('00000000-0000-0000-0000-000000000001'), $player1->getId(), $seasonId, 3200),
            new Score(new Uuid('00000000-0000-0000-0000-000000000002'), $player2->getId(), $seasonId, 2700),
        ];

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegionAndSeason')
            ->with(null, $seasonId, 10, 0)
            ->willReturn($scores);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('countBySeason')
            ->with($seasonId, null)
            ->willReturn(2);

        $this->playerRepositoryMock
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$player1->getId(), $player1],
                [$player2->getId(), $player2],
            ]);

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
        $seasonId = $this->activeSeason->getId();

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegionAndSeason')
            ->with(null, $seasonId, 10, 0)
            ->willReturn([]);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('countBySeason')
            ->with($seasonId, null)
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

        $this->scoreRepositoryMock
            ->expects($this->never())
            ->method('findTopByRegionAndSeason');

        // Act
        $result = $this->useCase->execute(10, 0);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result['entries']);
        $this->assertEquals('CachedPlayer', $result['entries'][0]['nickname']);
    }

    public function testGetGlobalLeaderboardWithPagination(): void {
        // Arrange
        $player = new Player(new PlayerId('player1'), 'PlayerOne', new Trophy(2000), 'US');
        $score = new Score(new Uuid('31111111-1111-1111-1111-111111111111'), new PlayerId('player1'), $this->seasonId, 2000);

        $seasonId = $this->activeSeason->getId();
        $scores = [new Score(new Uuid('00000000-0000-0000-0000-000000000003'), $player->getId(), $seasonId, 2100)];

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegionAndSeason')
            ->with(null, $seasonId, 5, 10)
            ->willReturn($scores);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('countBySeason')
            ->with($seasonId, null)
            ->willReturn(100);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($player->getId())
            ->willReturn($player);

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
        $seasonId = $this->activeSeason->getId();

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegionAndSeason')
            ->with(null, $seasonId, 500, 0) // Should be capped at 500
            ->willReturn([]);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('countBySeason')
            ->with($seasonId, null)
            ->willReturn(0);

        // Act
        $result = $this->useCase->execute(1000, 0); // Request 1000, should be capped

        // Assert
        $this->assertIsArray($result);
    }

    public function testGetGlobalLeaderboardWithDifferentTrophyLevels(): void {
        // Arrange
        $players = [];
        $expectedLevels = [2, 3, 3, 4];
        $expectedTrophies = [500, 1500, 2500, 4000];

        for ($i = 0; $i < 4; $i++) {
            $players[] = new Player(new PlayerId("player{$i}"), "Player{$i}", new Trophy($expectedTrophies[$i]), 'US');
            $scores[] = new Score(
                new Uuid(sprintf('41111111-1111-1111-1111-11111111111%d', $i)),
                new PlayerId("player{$i}"),
                $this->seasonId,
                $expectedTrophies[$i]
            );
        }

        $seasonId = $this->activeSeason->getId();
        $scores = [
            new Score(new Uuid('00000000-0000-0000-0000-000000000008'), $players[0]->getId(), $seasonId, 500),
            new Score(new Uuid('00000000-0000-0000-0000-000000000009'), $players[1]->getId(), $seasonId, 1500),
            new Score(new Uuid('00000000-0000-0000-0000-00000000000a'), $players[2]->getId(), $seasonId, 2500),
            new Score(new Uuid('00000000-0000-0000-0000-00000000000b'), $players[3]->getId(), $seasonId, 4000),
        ];

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegionAndSeason')
            ->willReturn($scores);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('countBySeason')
            ->with($seasonId, null)
            ->willReturn(4);

        $this->playerRepositoryMock
            ->expects($this->exactly(4))
            ->method('findById')
            ->willReturnMap(array_map(fn($p) => [$p->getId(), $p], $players));

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

    private function createActiveSeason(): Season {
        return new Season(
            new Uuid('11111111-1111-1111-1111-111111111111'),
            'Active Season',
            new \DateTime('-1 week'),
            new \DateTime('+1 week'),
            true
        );
    }
}
