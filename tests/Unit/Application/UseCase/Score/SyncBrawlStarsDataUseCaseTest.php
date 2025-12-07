<?php
namespace Tests\Unit\Application\UseCase\Score;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\Score\SyncBrawlStarsDataUseCase;
use App\Application\Service\BrawlStarsService;
use App\Application\Service\CacheService;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Entity\Player;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class SyncBrawlStarsDataUseCaseTest extends TestCase {
    private SyncBrawlStarsDataUseCase $useCase;
    private $brawlStarsServiceMock;
    private $playerRepositoryMock;
    private $cacheServiceMock;

    protected function setUp(): void {
        $this->playerRepositoryMock = $this->createMock(PlayerRepositoryInterface::class);
        $this->brawlStarsServiceMock = $this->createMock(BrawlStarsService::class);
        $this->cacheServiceMock = $this->createMock(CacheService::class);

        $this->useCase = new SyncBrawlStarsDataUseCase(
            $this->playerRepositoryMock,
            $this->brawlStarsServiceMock,
            $this->cacheServiceMock
        );
    }

    public function testSyncDataSuccessfullyCreatesNewPlayerAndScore(): void {
        // Arrange
        $playerId = 'player123';
        $brawlStarsData = [
            'id' => $playerId,
            'nickname' => 'TestPlayer',
            'totalTrophies' => 1500,
            'region' => 'US',
            'lastSyncedAt' => '2023-01-01T12:00:00Z'
        ];

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerData')
            ->with($playerId)
            ->willReturn($brawlStarsData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($playerId)
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($player) use ($playerId) {
                return $player->getId()->getValue() === $playerId &&
                       $player->getNickname() === 'TestPlayer' &&
                       $player->getTotalTrophies()->getValue() === 1500 &&
                       $player->getRegion() === 'US';
            }));

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findByPlayerAndSeason')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($score) use ($playerId) {
                return $score->getPlayerId()->getValue() === $playerId &&
                       $score->getTrophies()->getValue() === 1500;
            }));

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertTrue($result);
    }

    public function testSyncDataSuccessfullyUpdatesExistingPlayerAndCreatesNewScore(): void {
        // Arrange
        $playerId = 'player123';
        $existingPlayer = new Player(
            id: new PlayerId($playerId),
            nickname: 'OldNickname',
            totalTrophies: new Trophy(1000),
            region: 'EU'
        );

        $brawlStarsData = [
            'id' => $playerId,
            'nickname' => 'NewNickname',
            'totalTrophies' => 1500,
            'region' => 'US',
            'lastSyncedAt' => '2023-01-01T12:00:00Z'
        ];

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerData')
            ->with($playerId)
            ->willReturn($brawlStarsData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($playerId)
            ->willReturn($existingPlayer);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($player) use ($playerId) {
                return $player->getId()->getValue() === $playerId &&
                       $player->getNickname() === 'NewNickname' &&
                       $player->getTotalTrophies()->getValue() === 1500 &&
                       $player->getRegion() === 'US';
            }));

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findByPlayerAndSeason')
            ->willReturn(null);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($score) use ($playerId) {
                return $score->getPlayerId()->getValue() === $playerId &&
                       $score->getTrophies()->getValue() === 1500;
            }));

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertTrue($result);
    }

    public function testSyncDataSuccessfullyUpdatesExistingScore(): void {
        // Arrange
        $playerId = 'player123';
        $existingPlayer = new Player(
            id: new PlayerId($playerId),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1000),
            region: 'US'
        );

        $existingScore = new Score(
            id: $this->createMock(\App\Domain\ValueObject\Uuid::class),
            playerId: new PlayerId($playerId),
            season: $this->createMock(Season::class),
            trophies: new Trophy(1000),
            recordedAt: new \DateTime('2023-01-01')
        );

        $brawlStarsData = [
            'id' => $playerId,
            'nickname' => 'TestPlayer',
            'totalTrophies' => 1500,
            'region' => 'US',
            'lastSyncedAt' => '2023-01-01T12:00:00Z'
        ];

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getPlayerData')
            ->with($playerId)
            ->willReturn($brawlStarsData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($playerId)
            ->willReturn($existingPlayer);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save');

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('findByPlayerAndSeason')
            ->willReturn($existingScore);

        $this->scoreRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($score) use ($playerId) {
                return $score->getPlayerId()->getValue() === $playerId &&
                       $score->getTrophies()->getValue() === 1500;
            }));

        // Act
        $result = $this->useCase->execute($playerId);

        // Assert
        $this->assertTrue($result);
    }

    public function testSyncDataWithBrawlStarsServiceExceptionReturnsFalse(): void {
        // Arrange
        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getTopPlayers')
            ->with(1000)
            ->willThrowException(new \Exception('API Error'));

        $this->playerRepositoryMock
            ->expects($this->never())
            ->method('findById');

        $this->cacheServiceMock
            ->expects($this->never())
            ->method('flush');

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sync failed: API Error');

        $this->useCase->execute();
    }
}
