<?php
namespace App\Domain\Entity;

use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class Player {
    private PlayerId $id;
    private string $nickname;
    private Trophy $totalTrophies;
    private string $region;
    private \DateTime $lastSyncedAt;

    public function __construct(
        PlayerId $id,
        string $nickname,
        Trophy $totalTrophies,
        string $region,
        ?\DateTime $lastSyncedAt = null
    ) {
        if (empty($nickname)) {
            throw new \InvalidArgumentException('Nickname cannot be empty');
        }
        if (empty($region)) {
            throw new \InvalidArgumentException('Region is required');
        }

        $this->id = $id;
        $this->nickname = $nickname;
        $this->totalTrophies = $totalTrophies;
        $this->region = $region;
        $this->lastSyncedAt = $lastSyncedAt ?? new \DateTime();
    }

    // Business logic
    public function getLevel(): int {
        $trophyCount = $this->totalTrophies->getValue();
        
        if ($trophyCount < 500) return 1;
        if ($trophyCount < 1500) return 2;
        if ($trophyCount < 3000) return 3;
        return 4;
    }

    public function updateFromSync(int $newTrophies, string $newRegion, ?string $newNickname = null): void {
        if ($newTrophies < 0) {
            throw new \InvalidArgumentException('Invalid trophy update');
        }
        if ($newNickname !== null && $newNickname !== '') {
            $this->nickname = $newNickname;
        }

        $this->totalTrophies = new Trophy($newTrophies);
        $this->region = $newRegion;

        if ($newNickname !== null && $newNickname !== '') {
            $this->nickname = $newNickname;
        }
        $this->lastSyncedAt = new \DateTime();
    }

    // Getters
    public function getId(): PlayerId { 
        return $this->id; 
    }
    
    public function getNickname(): string { 
        return $this->nickname; 
    }
    
    public function getTotalTrophies(): Trophy { 
        return $this->totalTrophies; 
    }
    
    public function getRegion(): string { 
        return $this->region; 
    }
    
    public function getLastSyncedAt(): \DateTime { 
        return $this->lastSyncedAt; 
    }
}

