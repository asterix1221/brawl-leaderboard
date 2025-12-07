<?php
namespace Tests\Unit\Application\UseCase\Leaderboard;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Leaderboard\SearchPlayerUseCase;
use App\Application\DTO\PlayerDTO;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Entity\Player;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class SearchPlayerUseCaseTest extends TestCase {
    private SearchPlayerUseCase $useCase;
    private $playerRepositoryMock;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->useCase = new SearchPlayerUseCase($this->playerRepositoryMock);
    }

    public function testSearchPlayerSuccessfully(): void {
        // Arrange
        $query = 'Test';
        $players = [
            new Player(
                id: new PlayerId('player123'),
                nickname: 'TestPlayer',
                totalTrophies: new Trophy(1500),
                region: 'US'
            ),
            new Player(
                id: new PlayerId('player456'),
                nickname: 'TestMaster',
                totalTrophies: new Trophy(2000),
                region: 'EU'
            )
        ];

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('searchByNickname')
            ->with($query, 20)
            ->willReturn($players);

        // Act
        $result = $this->useCase->execute($query);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $firstPlayer = $result[0];
        $this->assertEquals('player123', $firstPlayer['id']);
        $this->assertEquals('TestPlayer', $firstPlayer['nickname']);
        $this->assertEquals(1500, $firstPlayer['totalTrophies']);
        $this->assertEquals('US', $firstPlayer['region']);
        $this->assertEquals(3, $firstPlayer['level']);

        $secondPlayer = $result[1];
        $this->assertEquals('player456', $secondPlayer['id']);
        $this->assertEquals('TestMaster', $secondPlayer['nickname']);
        $this->assertEquals(2000, $secondPlayer['totalTrophies']);
        $this->assertEquals('EU', $secondPlayer['region']);
        $this->assertEquals(3, $secondPlayer['level']); // 2000 trophies = level 3
    }

    public function testSearchPlayerWithEmptyResult(): void {
        // Arrange
        $query = 'NonExistent';

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('searchByNickname')
            ->with($query, 20)
            ->willReturn([]);

        // Act
        $result = $this->useCase->execute($query);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testSearchPlayerWithCustomLimit(): void {
        // Arrange
        $query = 'Test';
        $limit = 5;
        $players = [
            new Player(
                id: new PlayerId('player123'),
                nickname: 'TestPlayer',
                totalTrophies: new Trophy(1500),
                region: 'US'
            )
        ];

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('searchByNickname')
            ->with($query, $limit)
            ->willReturn($players);

        // Act
        $result = $this->useCase->execute($query, $limit);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        
        $playerData = $result[0];
        $this->assertEquals('player123', $playerData['id']);
        $this->assertEquals('TestPlayer', $playerData['nickname']);
        $this->assertEquals(1500, $playerData['totalTrophies']);
        $this->assertEquals('US', $playerData['region']);
        $this->assertEquals(3, $playerData['level']);
    }

    public function testSearchPlayerWithShortQueryThrowsException(): void {
        // Arrange
        $query = 'T'; // Less than 2 characters

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('searchByNickname');

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search query must be at least 2 characters');

        $this->useCase->execute($query);
    }

    public function testSearchPlayerWithEmptyQueryThrowsException(): void {
        // Arrange
        $query = '';

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('searchByNickname');

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search query must be at least 2 characters');

        $this->useCase->execute($query);
    }

    public function testSearchPlayerWithDifferentTrophyLevels(): void {
        // Arrange
        $query = 'Player';
        $players = [];
        $expectedLevels = [2, 3, 3, 4];
        $expectedTrophies = [500, 1500, 2500, 4000];

        for ($i = 0; $i < 4; $i++) {
            $players[] = new Player(
                id: new PlayerId("player{$i}"),
                nickname: "Player{$i}",
                totalTrophies: new Trophy($expectedTrophies[$i]),
                region: 'US'
            );
        }

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('searchByNickname')
            ->with($query, 20)
            ->willReturn($players);

        // Act
        $result = $this->useCase->execute($query);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        for ($i = 0; $i < 4; $i++) {
            $playerData = $result[$i];
            $this->assertEquals($expectedLevels[$i], $playerData['level']);
            $this->assertEquals($expectedTrophies[$i], $playerData['totalTrophies']);
        }
    }

    public function testSearchPlayerWithZeroTrophies(): void {
        // Arrange
        $query = 'Newbie';
        $players = [
            new Player(
                id: new PlayerId('player123'),
                nickname: 'NewbiePlayer',
                totalTrophies: new Trophy(0),
                region: 'US'
            )
        ];

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('searchByNickname')
            ->with($query, 20)
            ->willReturn($players);

        // Act
        $result = $this->useCase->execute($query);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        
        $playerData = $result[0];
        $this->assertEquals('player123', $playerData['id']);
        $this->assertEquals('NewbiePlayer', $playerData['nickname']);
        $this->assertEquals(0, $playerData['totalTrophies']);
        $this->assertEquals('US', $playerData['region']);
        $this->assertEquals(1, $playerData['level']);
    }

    public function testSearchPlayerWithNumericQuery(): void {
        // Arrange
        $query = '123';
        $players = [
            new Player(
                id: new PlayerId('player123'),
                nickname: '123Player',
                totalTrophies: new Trophy(1500),
                region: 'US'
            )
        ];

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('searchByNickname')
            ->with($query, 20)
            ->willReturn($players);

        // Act
        $result = $this->useCase->execute($query);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        
        $playerData = $result[0];
        $this->assertEquals('player123', $playerData['id']);
        $this->assertEquals('123Player', $playerData['nickname']);
        $this->assertEquals(1500, $playerData['totalTrophies']);
        $this->assertEquals('US', $playerData['region']);
        $this->assertEquals(3, $playerData['level']);
    }
}
