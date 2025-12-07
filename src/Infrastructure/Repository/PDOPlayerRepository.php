<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Player;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use \PDO;

class PDOPlayerRepository implements PlayerRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function findById(PlayerId $id): ?Player {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM players WHERE id = :id'
        );
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findByNickname(string $nickname): ?Player {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM players WHERE nickname = :nickname LIMIT 1'
        );
        $stmt->execute([':nickname' => $nickname]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findTopByTrophies(int $limit, int $offset): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM players 
             ORDER BY total_trophies DESC 
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function countAll(): int {
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM players');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }

    public function save(Player $player): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO players (id, nickname, total_trophies, region, last_updated)
             VALUES (:id, :nickname, :trophies, :region, :updated)
             ON CONFLICT(id) DO UPDATE SET
                nickname = excluded.nickname,
                total_trophies = excluded.total_trophies,
                region = excluded.region,
                last_updated = excluded.last_updated'
        );
        
        $stmt->execute([
            ':id' => $player->getId()->getValue(),
            ':nickname' => $player->getNickname(),
            ':trophies' => $player->getTotalTrophies()->getValue(),
            ':region' => $player->getRegion(),
            ':updated' => $player->getLastSyncedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(PlayerId $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM players WHERE id = :id');
        $stmt->execute([':id' => $id->getValue()]);
    }

    public function searchByNickname(string $query, int $limit): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM players 
             WHERE LOWER(nickname) LIKE LOWER(:query)
             ORDER BY total_trophies DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    private function mapToEntity(array $row): Player {
        return new Player(
            id: new PlayerId($row['id']),
            nickname: $row['nickname'],
            totalTrophies: new Trophy((int)$row['total_trophies']),
            region: $row['region'] ?? 'GLOBAL',
            lastSyncedAt: new \DateTime($row['last_updated'] ?? 'now')
        );
    }
}

