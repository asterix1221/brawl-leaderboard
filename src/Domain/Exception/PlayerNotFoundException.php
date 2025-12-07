<?php
namespace App\Domain\Exception;

class PlayerNotFoundException extends \Exception {
    public function __construct(string $message = "Player not found", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

