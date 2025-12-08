<?php

namespace Tests\Integration\Support;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Uuid;

class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private array $users = [];

    public function __construct(array $seed = [])
    {
        foreach ($seed as $user) {
            $this->save($user);
        }
    }

    public function findById(Uuid $id): ?User
    {
        return $this->users[$id->getValue()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail()->getValue() === $email->getValue()) {
                return $user;
            }
        }

        return null;
    }

    public function save(User $user): void
    {
        $this->users[$user->getId()->getValue()] = $user;
    }

    public function delete(Uuid $id): void
    {
        unset($this->users[$id->getValue()]);
    }
}
