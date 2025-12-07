<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\Email;
use \PDO;

class PDOUserRepository implements UserRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function findById(Uuid $id): ?User {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findByEmail(Email $email): ?User {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function save(User $user): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (id, email, password_hash, nickname, created_at, updated_at)
             VALUES (:id, :email, :password_hash, :nickname, :created_at, :updated_at)
             ON CONFLICT(id) DO UPDATE SET
                email = excluded.email,
                password_hash = excluded.password_hash,
                nickname = excluded.nickname,
                updated_at = excluded.updated_at'
        );
        
        $stmt->execute([
            ':id' => $user->getId()->getValue(),
            ':email' => $user->getEmail()->getValue(),
            ':password_hash' => $this->getPasswordHash($user),
            ':nickname' => $user->getNickname(),
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(Uuid $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id->getValue()]);
    }

    private function mapToEntity(array $row): User {
        return new User(
            id: new Uuid($row['id']),
            email: new Email($row['email']),
            passwordHash: $row['password_hash'],
            nickname: $row['nickname'] ?? null,
            createdAt: new \DateTime($row['created_at']),
            updatedAt: new \DateTime($row['updated_at'])
        );
    }

    // Helper method to access password hash (only for repository)
    private function getPasswordHash(User $user): string {
        // Use reflection to access private property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('passwordHash');
        $property->setAccessible(true);
        return $property->getValue($user);
    }
}

