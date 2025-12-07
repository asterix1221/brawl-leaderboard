<?php
namespace App\Domain\Exception;

class InvalidCredentialsException extends \Exception {
    public function __construct(string $message = "Invalid credentials", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

