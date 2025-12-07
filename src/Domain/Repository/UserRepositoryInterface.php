<?php
namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\Email;

interface UserRepositoryInterface {
    public function findById(Uuid $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function delete(Uuid $id): void;
}

