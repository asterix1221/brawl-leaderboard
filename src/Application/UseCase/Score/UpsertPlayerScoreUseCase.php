<?php
namespace App\Application\UseCase\Score;

use App\Application\Service\CacheService;
use App\Domain\Entity\Score;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\ScoreRepositoryInterface;
use App\Domain\Repository\SeasonRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Uuid;

class UpsertPlayerScoreUseCase {
    public function __construct(
        private ScoreRepositoryInterface $scoreRepository,
        private PlayerRepositoryInterface $playerRepository,
        private SeasonRepositoryInterface $seasonRepository,
        private CacheService $cacheService
    ) {}

    public function execute(
        string $playerId,
        ?string $seasonId,
        int $totalScore,
        int $wins,
        int $losses
    ): array {
        $playerIdVo = new PlayerId($playerId);

        $player = $this->playerRepository->findById($playerIdVo);
        if ($player === null) {
            throw new \InvalidArgumentException('Player not found');
        }

        $seasonUuid = null;
        if ($seasonId !== null) {
            $seasonUuid = new Uuid($seasonId);
        } else {
            $activeSeason = $this->seasonRepository->findActive();
            if ($activeSeason !== null) {
                $seasonUuid = $activeSeason->getId();
            } else {
                throw new \InvalidArgumentException('Season ID is required when no active season exists');
            }
        }

        $existingScore = $this->scoreRepository->findByPlayerAndSeason($playerIdVo, $seasonUuid);

        if ($existingScore !== null) {
            $existingScore->update($totalScore, $wins, $losses);
            $score = $existingScore;
        } else {
            $score = new Score(
                id: Uuid::generate(),
                playerId: $playerIdVo,
                seasonId: $seasonUuid,
                totalScore: $totalScore,
                wins: $wins,
                losses: $losses
            );
        }

        $this->scoreRepository->save($score);

        try {
            $this->cacheService->flush();
        } catch (\Exception $e) {
            error_log('Cache flush error: ' . $e->getMessage());
        }

        return [
            'scoreId' => $score->getId()->getValue(),
            'playerId' => $score->getPlayerId()->getValue(),
            'seasonId' => $score->getSeasonId()->getValue(),
            'totalScore' => $score->getTotalScore(),
            'wins' => $score->getWins(),
            'losses' => $score->getLosses(),
            'updatedAt' => $score->getUpdatedAt()->format('c')
        ];
    }
}
