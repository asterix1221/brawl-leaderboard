<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Score;
use App\Domain\Repository\ScoreRepositoryInterface;
use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\PlayerId;
use \PDO;

class PDOScoreRepository implements ScoreRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function findById(Uuid $id): ?Score {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM scores WHERE id = :id'
        );
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findByPlayerAndSeason(PlayerId $playerId, Uuid $seasonId): ?Score {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM scores WHERE player_id = :player_id AND season_id = :season_id LIMIT 1'
        );
        $stmt->execute([
            ':player_id' => $playerId->getValue(),
            ':season_id' => $seasonId->getValue()
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->mapToEntity($row);
    }

    public function findByPlayer(PlayerId $playerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM scores WHERE player_id = :player_id ORDER BY created_at DESC'
        );
        $stmt->execute([':player_id' => $playerId->getValue()]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function save(Score $score): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO scores (id, player_id, season_id, total_score, wins, losses, created_at, updated_at)
             VALUES (:id, :player_id, :season_id, :total_score, :wins, :losses, :created_at, :updated_at)
             ON CONFLICT(id) DO UPDATE SET
                total_score = excluded.total_score,
                wins = excluded.wins,
                losses = excluded.losses,
                updated_at = excluded.updated_at'
        );
        
        $stmt->execute([
            ':id' => $score->getId()->getValue(),
            ':player_id' => $score->getPlayerId()->getValue(),
            ':season_id' => $score->getSeasonId()->getValue(),
            ':total_score' => $score->getTotalScore(),
            ':wins' => $score->getWins(),
            ':losses' => $score->getLosses(),
            ':created_at' => $score->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $score->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(Uuid $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM scores WHERE id = :id');
        $stmt->execute([':id' => $id->getValue()]);
    }

    private function mapToEntity(array $row): Score {
        return new Score(
            id: new Uuid($row['id']),
            playerId: new \App\Domain\ValueObject\PlayerId($row['player_id']),
            seasonId: new Uuid($row['season_id']),
            totalScore: (int)$row['total_score'],
            wins: (int)$row['wins'],
            losses: (int)$row['losses'],
            createdAt: new \DateTime($row['created_at']),
            updatedAt: new \DateTime($row['updated_at'])
        );
    }
}

