<?php
namespace App\Domain\Exception;

class InvalidScoreException extends \Exception {
    public function __construct(string $message = "Invalid score value", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

