<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Score;
use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\PlayerId;

interface ScoreRepositoryInterface {
    public function findById(Uuid $id): ?Score;
    public function findByPlayerAndSeason(PlayerId $playerId, Uuid $seasonId): ?Score;
    public function findByPlayer(PlayerId $playerId): array;
    public function findTopByRegionAndSeason(?string $region, ?Uuid $seasonId, int $limit = 50, int $offset = 0): array;
    public function countBySeason(?Uuid $seasonId, ?string $region = null): int;
    public function save(Score $score): void;
    public function delete(Uuid $id): void;
}

