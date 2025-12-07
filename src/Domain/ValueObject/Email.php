<?php
namespace App\Domain\ValueObject;

class Email {
    private string $value;

    public function __construct(string $value) {
        $filtered = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($filtered === false) {
            throw new \InvalidArgumentException('Invalid email format: ' . $value);
        }
        $this->value = $filtered;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function equals(Email $other): bool {
        return $this->value === $other->getValue();
    }
}

