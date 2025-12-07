<?php
namespace App\Domain\Repository;

use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\PlayerId;

interface ScoreHistoryRepositoryInterface {
    public function findByPlayer(PlayerId $playerId, int $limit = 100): array;
    public function findByScoreId(Uuid $scoreId): array;
    public function save(array $historyData): void;
}

