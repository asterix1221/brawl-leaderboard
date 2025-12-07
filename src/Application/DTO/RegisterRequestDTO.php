<?php
namespace App\Application\DTO;

class RegisterRequestDTO {
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $nickname = null
    ) {}

    public function validate(): array {
        $errors = [];

        if (empty($this->email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($this->password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($this->password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        return $errors;
    }

    public function toArray(): array {
        return [
            'email' => $this->email,
            'nickname' => $this->nickname
        ];
    }
}

