<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Season;
use App\Domain\ValueObject\Uuid;

interface SeasonRepositoryInterface {
    public function findById(Uuid $id): ?Season;
    public function findActive(): ?Season;
    public function findAll(): array;
    public function save(Season $season): void;
    public function delete(Uuid $id): void;
}

