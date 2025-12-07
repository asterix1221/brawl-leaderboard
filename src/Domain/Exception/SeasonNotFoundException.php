<?php
namespace App\Domain\Exception;

class SeasonNotFoundException extends \Exception {
    public function __construct(string $message = "Season not found", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

