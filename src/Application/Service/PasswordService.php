<?php
namespace App\Application\Service;

class PasswordService {
    public function hash(string $password): string {
        $algorithm = defined('PASSWORD_ARGON2ID') ? \PASSWORD_ARGON2ID : \PASSWORD_BCRYPT;
        return password_hash($password, $algorithm);
    }

    public function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function validatePasswordStrength(string $password): array {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        return $errors;
    }
}

