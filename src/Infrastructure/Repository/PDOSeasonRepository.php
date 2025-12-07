<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Season;
use App\Domain\Repository\SeasonRepositoryInterface;
use App\Domain\ValueObject\Uuid;
use \PDO;

class PDOSeasonRepository implements SeasonRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function findById(Uuid $id): ?Season {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM seasons WHERE id = :id'
        );
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findActive(): ?Season {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM seasons WHERE is_active = true 
             AND start_date <= NOW() AND end_date >= NOW()
             ORDER BY start_date DESC LIMIT 1'
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findAll(): array {
        $stmt = $this->pdo->query('SELECT * FROM seasons ORDER BY start_date DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(Season $season): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO seasons (id, name, start_date, end_date, is_active, created_at)
             VALUES (:id, :name, :start_date, :end_date, :is_active, :created_at)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                start_date = excluded.start_date,
                end_date = excluded.end_date,
                is_active = excluded.is_active'
        );
        
        $stmt->execute([
            ':id' => $season->getId()->getValue(),
            ':name' => $season->getName(),
            ':start_date' => $season->getStartDate()->format('Y-m-d H:i:s'),
            ':end_date' => $season->getEndDate()->format('Y-m-d H:i:s'),
            ':is_active' => $season->getIsActive() ? 't' : 'f',
            ':created_at' => $season->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(Uuid $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM seasons WHERE id = :id');
        $stmt->execute([':id' => $id->getValue()]);
    }

    private function mapToEntity(array $row): Season {
        return new Season(
            id: new Uuid($row['id']),
            name: $row['name'],
            startDate: new \DateTime($row['start_date']),
            endDate: new \DateTime($row['end_date']),
            isActive: $row['is_active'] === 't' || $row['is_active'] === true,
            createdAt: new \DateTime($row['created_at'])
        );
    }
}

