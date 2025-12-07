<?php
namespace App\Domain\ValueObject;

class PlayerId {
    private string $value;

    public function __construct(string $value) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Player ID cannot be empty');
        }
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function equals(PlayerId $other): bool {
        return $this->value === $other->getValue();
    }
}

