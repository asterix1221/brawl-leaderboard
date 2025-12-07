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
        $brawlStarsData = [
            [
                'tag' => 'player123',
                'name' => 'TestPlayer',
                'trophies' => 1500,
                'club' => ['tag' => 'GLOBAL']
            ]
        ];

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getTopPlayers')
            ->with(1000)
            ->willReturn($brawlStarsData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId('player123')))
            ->willReturn(null);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($player) {
                return $player->getId()->getValue() === 'player123' &&
                       $player->getNickname() === 'TestPlayer' &&
                       $player->getTotalTrophies()->getValue() === 1500 &&
                       $player->getRegion() === 'GLOBAL';
            }));

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->useCase->execute();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['synced']);
        $this->assertEquals(0, $result['errors']);
        $this->assertArrayHasKey('timestamp', $result);
    }

    public function testSyncDataSuccessfullyUpdatesExistingPlayerAndCreatesNewScore(): void {
        // Arrange
        $existingPlayer = new Player(
            new PlayerId('player123'),
            'OldNickname',
            new Trophy(1000),
            'EU'
        );

        $brawlStarsData = [
            [
                'tag' => 'player123',
                'name' => 'NewNickname',
                'trophies' => 1500,
                'club' => ['tag' => 'GLOBAL']
            ]
        ];

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getTopPlayers')
            ->with(1000)
            ->willReturn($brawlStarsData);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(new PlayerId('player123')))
            ->willReturn($existingPlayer);

        $this->playerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($player) {
                return $player->getId()->getValue() === 'player123' &&
                       $player->getNickname() === 'NewNickname' &&
                       $player->getTotalTrophies()->getValue() === 1500 &&
                       $player->getRegion() === 'GLOBAL';
            }));

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->useCase->execute();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['synced']);
        $this->assertEquals(0, $result['errors']);
        $this->assertArrayHasKey('timestamp', $result);
    }

    public function testSyncDataSuccessfullyHandlesMultiplePlayersWithErrors(): void {
        // Arrange
        $brawlStarsData = [
            [
                'tag' => 'player123',
                'name' => 'TestPlayer1',
                'trophies' => 1500,
                'club' => ['tag' => 'GLOBAL']
            ],
            [
                'tag' => 'player456', // This will cause an error
                'name' => 'TestPlayer2',
                'trophies' => 2000,
                'club' => ['tag' => 'GLOBAL']
            ]
        ];

        $this->brawlStarsServiceMock
            ->expects($this->once())
            ->method('getTopPlayers')
            ->with(1000)
            ->willReturn($brawlStarsData);

        $this->playerRepositoryMock
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(function($playerId) {
                return $playerId->getValue() === 'player123' ? null : null;
            });

        // First save succeeds, second save throws exception
        $this->playerRepositoryMock
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function($player) {
                if ($player->getId()->getValue() === 'player456') {
                    throw new \Exception('Database error');
                }
            });

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->useCase->execute();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['synced']); // Only first player synced
        $this->assertEquals(1, $result['errors']); // Second player error
        $this->assertArrayHasKey('timestamp', $result);
    }

    public function testSyncDataWithBrawlStarsServiceExceptionThrowsRuntimeException(): void {
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
