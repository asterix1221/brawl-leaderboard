<?php
namespace App\Application\UseCase\Leaderboard;

use App\Application\DTO\LeaderboardEntryDTO;
use App\Application\Service\CacheService;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\ScoreRepositoryInterface;
use App\Domain\Repository\SeasonRepositoryInterface;
use App\Domain\ValueObject\Uuid;

class GetGlobalLeaderboardUseCase {
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private ScoreRepositoryInterface $scoreRepository,
        private SeasonRepositoryInterface $seasonRepository,
        private CacheService $cacheService
    ) {}

    public function execute(
        int $limit = 100,
        int $offset = 0,
        ?string $region = null,
        ?string $seasonId = null
    ): array {
        // Validate input
        $limit = min($limit, 500);
        $offset = max(0, $offset);

        // Resolve season
        $seasonUuid = null;
        if ($seasonId !== null) {
            $seasonUuid = new Uuid($seasonId);
        } else {
            $activeSeason = $this->seasonRepository->findActive();
            if ($activeSeason !== null) {
                $seasonUuid = $activeSeason->getId();
            } else {
                throw new \InvalidArgumentException('Season is required when no active season exists');
            }
        }

        // Generate cache key
        $seasonKey = $seasonUuid?->getValue() ?? 'all';
        $cacheKey = 'leaderboard:' . ($region ?? 'global') . ":{$seasonKey}:{$offset}:{$limit}";

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
        $scores = $this->scoreRepository->findTopByRegionAndSeason($region, $seasonUuid, $limit, $offset);
        $total = $this->scoreRepository->countBySeason($seasonUuid, $region);

        $entries = [];
        foreach ($scores as $idx => $score) {
            $player = $this->playerRepository->findById($score->getPlayerId());

            if ($player === null) {
                continue;
            }

            $entries[] = new LeaderboardEntryDTO(
                rank: $offset + $idx + 1,
                playerId: $player->getId()->getValue(),
                nickname: $player->getNickname(),
                totalTrophies: $player->getTotalTrophies()->getValue(),
                totalScore: $score->getTotalScore(),
                region: $player->getRegion(),
                level: $player->getLevel()
            );
        }

        $response = [
            'entries' => array_map(fn($e) => $e->toArray(), $entries),
            'total' => $total,
            'hasMore' => $offset + $limit < $total,
            'page' => intval($offset / $limit) + 1,
            'seasonId' => $seasonUuid?->getValue()
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

