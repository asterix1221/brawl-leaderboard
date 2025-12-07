<?php
namespace App\Application\DTO;

class LoginRequestDTO {
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    public function toArray(): array {
        return [
            'email' => $this->email
        ];
    }
}

