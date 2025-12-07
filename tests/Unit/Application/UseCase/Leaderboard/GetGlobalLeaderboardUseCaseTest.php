<?php
namespace Tests\Unit\Application\UseCase\Leaderboard;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Application\DTO\LeaderboardRequestDTO;
use App\Application\DTO\LeaderboardResponseDTO;
use App\Domain\Repository\ScoreRepositoryInterface;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Entity\Score;
use App\Domain\Entity\Player;
use App\Domain\Entity\Season;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class GetGlobalLeaderboardUseCaseTest extends TestCase {
    private GetGlobalLeaderboardUseCase $useCase;
    private $scoreRepositoryMock;
    private $playerRepositoryMock;

    protected function setUp(): void {
        $this->scoreRepositoryMock = $this->createMock(ScoreRepositoryInterface::class);
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);

        $this->useCase = new GetGlobalLeaderboardUseCase(
            $this->scoreRepositoryMock,
            $this->playerRepositoryMock
        );
    }

    public function testGetGlobalLeaderboardSuccessfully(): void {
        // Arrange
        $request = new LeaderboardRequestDTO(
            region: 'US',
            limit: 10,
            offset: 0
        );

        $season = $this->createMock(Season::class);
        $season->method('getId')->willReturn('season1');

        $score1 = new Score(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            playerId: new PlayerId('player1'),
            season: $season,
            trophies: new Trophy(3000),
            recordedAt: new \DateTime('2023-01-01')
        );

        $score2 = new Score(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            playerId: new PlayerId('player2'),
            season: $season,
            trophies: new Trophy(2500),
            recordedAt: new \DateTime('2023-01-01')
        );

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

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegion')
            ->with('US', 10, 0)
            ->willReturn([$score1, $score2]);

        $this->playerRepositoryMock
            ->expects($this->exactly(2))
            ->method('findById')
            ->withConsecutive(['player1'], ['player2'])
            ->willReturnOnConsecutiveCalls($player1, $player2);

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LeaderboardResponseDTO::class, $result);
        $this->assertCount(2, $result->players);
        
        $firstPlayer = $result->players[0];
        $this->assertEquals('player1', $firstPlayer['id']);
        $this->assertEquals('PlayerOne', $firstPlayer['nickname']);
        $this->assertEquals(3000, $firstPlayer['trophies']);
        $this->assertEquals(1, $firstPlayer['rank']);
        $this->assertEquals(4, $firstPlayer['level']);

        $secondPlayer = $result->players[1];
        $this->assertEquals('player2', $secondPlayer['id']);
        $this->assertEquals('PlayerTwo', $secondPlayer['nickname']);
        $this->assertEquals(2500, $secondPlayer['trophies']);
        $this->assertEquals(2, $secondPlayer['rank']);
        $this->assertEquals(3, $secondPlayer['level']);
    }

    public function testGetGlobalLeaderboardWithEmptyResult(): void {
        // Arrange
        $request = new LeaderboardRequestDTO(
            region: 'EU',
            limit: 10,
            offset: 0
        );

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegion')
            ->with('EU', 10, 0)
            ->willReturn([]);

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('findById');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LeaderboardResponseDTO::class, $result);
        $this->assertCount(0, $result->players);
    }

    public function testGetGlobalLeaderboardWithPagination(): void {
        // Arrange
        $request = new LeaderboardRequestDTO(
            region: 'US',
            limit: 5,
            offset: 10
        );

        $season = $this->createMock(Season::class);
        $season->method('getId')->willReturn('season1');

        $score = new Score(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            playerId: new PlayerId('player1'),
            season: $season,
            trophies: new Trophy(2000),
            recordedAt: new \DateTime('2023-01-01')
        );

        $player = new Player(
            id: new PlayerId('player1'),
            nickname: 'PlayerOne',
            totalTrophies: new Trophy(2000),
            region: 'US'
        );

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegion')
            ->with('US', 5, 10)
            ->willReturn([$score]);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with('player1')
            ->willReturn($player);

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LeaderboardResponseDTO::class, $result);
        $this->assertCount(1, $result->players);
        
        $playerData = $result->players[0];
        $this->assertEquals('player1', $playerData['id']);
        $this->assertEquals('PlayerOne', $playerData['nickname']);
        $this->assertEquals(2000, $playerData['trophies']);
        $this->assertEquals(1, $playerData['rank']); // Rank is relative to returned results
        $this->assertEquals(3, $playerData['level']);
    }

    public function testGetGlobalLeaderboardWithPlayerNotFound(): void {
        // Arrange
        $request = new LeaderboardRequestDTO(
            region: 'US',
            limit: 10,
            offset: 0
        );

        $season = $this->createMock(Season::class);
        $season->method('getId')->willReturn('season1');

        $score = new Score(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            playerId: new PlayerId('player1'),
            season: $season,
            trophies: new Trophy(1500),
            recordedAt: new \DateTime('2023-01-01')
        );

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegion')
            ->with('US', 10, 0)
            ->willReturn([$score]);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with('player1')
            ->willReturn(null);

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LeaderboardResponseDTO::class, $result);
        $this->assertCount(0, $result->players); // Player not found, so filtered out
    }

    public function testGetGlobalLeaderboardWithDefaultParameters(): void {
        // Arrange
        $request = new LeaderboardRequestDTO(
            region: null,
            limit: null,
            offset: null
        );

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegion')
            ->with(null, 50, 0) // Default values
            ->willReturn([]);

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('findById');

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LeaderboardResponseDTO::class, $result);
        $this->assertCount(0, $result->players);
    }

    public function testGetGlobalLeaderboardWithDifferentTrophyLevels(): void {
        // Arrange
        $request = new LeaderboardRequestDTO(
            region: 'US',
            limit: 10,
            offset: 0
        );

        $season = $this->createMock(Season::class);
        $season->method('getId')->willReturn('season1');

        $scores = [];
        $players = [];
        $expectedLevels = [1, 2, 3, 4];
        $expectedTrophies = [500, 1500, 2500, 4000];

        for ($i = 0; $i < 4; $i++) {
            $scores[] = new Score(
                id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
                playerId: new PlayerId("player{$i}"),
                season: $season,
                trophies: new Trophy($expectedTrophies[$i]),
                recordedAt: new \DateTime('2023-01-01')
            );

            $players[] = new Player(
                id: new PlayerId("player{$i}"),
                nickname: "Player{$i}",
                totalTrophies: new Trophy($expectedTrophies[$i]),
                region: 'US'
            );
        }

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findTopByRegion')
            ->willReturn($scores);

        $this->playerRepositoryMock
            ->expects($this->exactly(4))
            ->method('findById')
            ->willReturn(...$players);

        // Act
        $result = $this->useCase->execute($request);

        // Assert
        $this->assertInstanceOf(LeaderboardResponseDTO::class, $result);
        $this->assertCount(4, $result->players);

        for ($i = 0; $i < 4; $i++) {
            $playerData = $result->players[$i];
            $this->assertEquals($expectedLevels[$i], $playerData['level']);
            $this->assertEquals($expectedTrophies[$i], $playerData['trophies']);
        }
    }
}
