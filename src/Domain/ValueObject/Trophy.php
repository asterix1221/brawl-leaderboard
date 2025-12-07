<?php
namespace App\Domain\ValueObject;

class Trophy {
    private int $value;

    public function __construct(int $value) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Trophy count cannot be negative');
        }
        $this->value = $value;
    }

    public function getValue(): int {
        return $this->value;
    }

    public function equals(Trophy $other): bool {
        return $this->value === $other->getValue();
    }
}

