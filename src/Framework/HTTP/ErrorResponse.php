<?php
namespace App\Framework\HTTP;

class ErrorResponse extends JsonResponse {
    public function __construct(string $message, int $statusCode = 400) {
        parent::__construct([
            'success' => false,
            'error' => $message,
            'code' => $statusCode
        ], $statusCode);
    }
}

