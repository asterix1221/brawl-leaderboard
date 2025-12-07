<?php
namespace App\Application\DTO;

class LoginResponseDTO {
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly array $user = []
    ) {}

    public function toArray(): array {
        return [
            'accessToken' => $this->accessToken,
            'refreshToken' => $this->refreshToken,
            'user' => $this->user
        ];
    }
}

