<?php
namespace App\Domain\Entity;

use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\PlayerId;

class Score {
    private Uuid $id;
    private PlayerId $playerId;
    private Uuid $seasonId;
    private int $totalScore;
    private int $wins;
    private int $losses;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        Uuid $id,
        PlayerId $playerId,
        Uuid $seasonId,
        int $totalScore = 0,
        int $wins = 0,
        int $losses = 0,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        if ($totalScore < 0) {
            throw new \InvalidArgumentException('Total score cannot be negative');
        }
        if ($wins < 0) {
            throw new \InvalidArgumentException('Wins cannot be negative');
        }
        if ($losses < 0) {
            throw new \InvalidArgumentException('Losses cannot be negative');
        }

        $this->id = $id;
        $this->playerId = $playerId;
        $this->seasonId = $seasonId;
        $this->totalScore = $totalScore;
        $this->wins = $wins;
        $this->losses = $losses;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    public function update(int $totalScore, int $wins, int $losses): void {
        if ($totalScore < 0 || $wins < 0 || $losses < 0) {
            throw new \InvalidArgumentException('Score values cannot be negative');
        }
        $this->totalScore = $totalScore;
        $this->wins = $wins;
        $this->losses = $losses;
        $this->updatedAt = new \DateTime();
    }

    public function calculateWinRate(): float {
        $totalGames = $this->wins + $this->losses;
        if ($totalGames === 0) {
            return 0.0;
        }
        return ($this->wins / $totalGames) * 100;
    }

    // Getters
    public function getId(): Uuid { 
        return $this->id; 
    }
    
    public function getPlayerId(): PlayerId { 
        return $this->playerId; 
    }
    
    public function getSeasonId(): Uuid { 
        return $this->seasonId; 
    }
    
    public function getTotalScore(): int { 
        return $this->totalScore; 
    }
    
    public function getWins(): int { 
        return $this->wins; 
    }
    
    public function getLosses(): int { 
        return $this->losses; 
    }
    
    public function getCreatedAt(): \DateTime { 
        return $this->createdAt; 
    }
    
    public function getUpdatedAt(): \DateTime { 
        return $this->updatedAt; 
    }
}

