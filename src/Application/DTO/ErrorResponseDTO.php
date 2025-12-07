<?php
namespace App\Application\DTO;

class ErrorResponseDTO {
    public function __construct(
        public readonly string $error,
        public readonly int $code = 400,
        public readonly bool $success = false
    ) {}

    public function toArray(): array {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'code' => $this->code
        ];
    }
}

