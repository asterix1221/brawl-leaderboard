<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Player;
use App\Domain\ValueObject\PlayerId;

interface PlayerRepositoryInterface {
    public function findById(PlayerId $id): ?Player;
    public function findByNickname(string $nickname): ?Player;
    public function findTopByTrophies(int $limit, int $offset): array;
    public function countAll(): int;
    public function save(Player $player): void;
    public function delete(PlayerId $id): void;
    public function searchByNickname(string $query, int $limit): array;
}

