<?php
namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;

class Uuid {
    private string $value;

    public function __construct(string $value = null) {
        if ($value === null) {
            $this->value = RamseyUuid::uuid4()->toString();
        } else {
            if (!RamseyUuid::isValid($value)) {
                throw new \InvalidArgumentException('Invalid UUID format: ' . $value);
            }
            $this->value = $value;
        }
    }

    public function getValue(): string {
        return $this->value;
    }

    public function equals(Uuid $other): bool {
        return $this->value === $other->getValue();
    }

    public static function generate(): self {
        return new self();
    }
}

