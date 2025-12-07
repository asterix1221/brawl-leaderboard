<?php
namespace App\Application\UseCase\Score;

use App\Domain\Entity\Player;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Application\Service\BrawlStarsService;
use App\Application\Service\CacheService;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class SyncBrawlStarsDataUseCase {
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private BrawlStarsService $brawlStarsService,
        private CacheService $cacheService
    ) {}

    public function execute(): array {
        $synced = 0;
        $errors = 0;

        try {
            // Get top players from Brawl Stars API
            $topPlayers = $this->brawlStarsService->getTopPlayers(1000);

            foreach ($topPlayers as $apiPlayer) {
                try {
                    $playerId = new PlayerId($apiPlayer['tag'] ?? '');
                    $existingPlayer = $this->playerRepository->findById($playerId);

                    if ($existingPlayer === null) {
                        // Create new player
                        $player = new Player(
                            $playerId,
                            $apiPlayer['name'] ?? 'Unknown',
                            new Trophy($apiPlayer['trophies'] ?? 0),
                            isset($apiPlayer['club']['tag']) ? $apiPlayer['club']['tag'] : 'GLOBAL'
                        );
                    } else {
                        // Update existing player
                        $existingPlayer->updateFromSync(
                            $apiPlayer['trophies'] ?? 0,
                            isset($apiPlayer['club']['tag']) ? $apiPlayer['club']['tag'] : 'GLOBAL'
                        );
                        $player = $existingPlayer;
                    }

                    $this->playerRepository->save($player);
                    $synced++;
                } catch (\Exception $e) {
                    $errors++;
                    error_log('Error syncing player: ' . $e->getMessage());
                }
            }

            // Invalidate cache
            $this->cacheService->flush();

        } catch (\Exception $e) {
            throw new \RuntimeException('Sync failed: ' . $e->getMessage());
        }

        return [
            'synced' => $synced,
            'errors' => $errors,
            'timestamp' => date('c')
        ];
    }
}

