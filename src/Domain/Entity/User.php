<?php
namespace App\Domain\Entity;

use App\Domain\ValueObject\Uuid;
use App\Domain\ValueObject\Email;

class User {
    private Uuid $id;
    private Email $email;
    private string $passwordHash;
    private ?string $nickname;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        Uuid $id,
        Email $email,
        string $passwordHash,
        ?string $nickname = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        if (empty($passwordHash)) {
            throw new \InvalidArgumentException('Password hash cannot be empty');
        }

        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->nickname = $nickname;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    // Getters (НЕ возвращаем passwordHash!)
    public function getId(): Uuid { 
        return $this->id; 
    }
    
    public function getEmail(): Email { 
        return $this->email; 
    }
    
    public function getNickname(): ?string { 
        return $this->nickname; 
    }
    
    public function getCreatedAt(): \DateTime { 
        return $this->createdAt; 
    }
    
    public function getUpdatedAt(): \DateTime { 
        return $this->updatedAt; 
    }

    // Internal method for password verification (only used by services)
    public function verifyPassword(string $password, callable $verifier): bool {
        return $verifier($password, $this->passwordHash);
    }
}

