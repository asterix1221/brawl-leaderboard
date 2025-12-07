<?php
namespace App\Domain\Entity;

use App\Domain\ValueObject\Uuid;

class Season {
    private Uuid $id;
    private string $name;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private bool $isActive;
    private \DateTime $createdAt;

    public function __construct(
        Uuid $id,
        string $name,
        \DateTime $startDate,
        \DateTime $endDate,
        bool $isActive = true,
        \DateTime $createdAt = null
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Season name cannot be empty');
        }
        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date cannot be before start date');
        }

        $this->id = $id;
        $this->name = $name;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new \DateTime();
    }

    public function isActive(): bool {
        if (!$this->isActive) {
            return false;
        }
        $now = new \DateTime();
        return $now >= $this->startDate && $now <= $this->endDate;
    }

    public function isEnded(): bool {
        $now = new \DateTime();
        return $now > $this->endDate;
    }

    public function getDuration(): \DateInterval {
        return $this->startDate->diff($this->endDate);
    }

    // Getters
    public function getId(): Uuid { 
        return $this->id; 
    }
    
    public function getName(): string { 
        return $this->name; 
    }
    
    public function getStartDate(): \DateTime { 
        return $this->startDate; 
    }
    
    public function getEndDate(): \DateTime { 
        return $this->endDate; 
    }
    
    public function getIsActive(): bool { 
        return $this->isActive; 
    }
    
    public function getCreatedAt(): \DateTime { 
        return $this->createdAt; 
    }
}

