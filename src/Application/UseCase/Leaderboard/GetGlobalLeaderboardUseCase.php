<?php
namespace App\Application\UseCase\Leaderboard;

use App\Application\DTO\LeaderboardEntryDTO;
use App\Application\Service\CacheService;
use App\Domain\Repository\PlayerRepositoryInterface;

class GetGlobalLeaderboardUseCase {
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private CacheService $cacheService
    ) {}

    public function execute(
        int $limit = 100,
        int $offset = 0,
        ?string $region = null
    ): array {
        // Validate input
        $limit = min($limit, 500);
        $offset = max(0, $offset);

        // Generate cache key
        $cacheKey = 'leaderboard:' . ($region ?? 'global') . ":{$offset}:{$limit}";

        // Try Redis cache first
        try {
            $cached = $this->cacheService->get($cacheKey);
            if ($cached !== null) {
                return json_decode($cached, true);
            }
        } catch (\Exception $e) {
            // Log error, continue without cache
            error_log('Cache read error: ' . $e->getMessage());
        }

        // Fetch from database
        $players = $this->playerRepository->findTopByTrophies($limit, $offset);
        $total = $this->playerRepository->countAll();

        $entries = [];
        foreach ($players as $idx => $player) {
            $entries[] = new LeaderboardEntryDTO(
                rank: $offset + $idx + 1,
                playerId: $player->getId()->getValue(),
                nickname: $player->getNickname(),
                totalTrophies: $player->getTotalTrophies()->getValue(),
                region: $player->getRegion(),
                level: $player->getLevel()
            );
        }

        $response = [
            'entries' => array_map(fn($e) => $e->toArray(), $entries),
            'total' => $total,
            'hasMore' => $offset + $limit < $total,
            'page' => intval($offset / $limit) + 1
        ];

        // Cache for 5 minutes
        try {
            $this->cacheService->set($cacheKey, json_encode($response), 300);
        } catch (\Exception $e) {
            error_log('Cache set error: ' . $e->getMessage());
        }

        return $response;
    }
}

