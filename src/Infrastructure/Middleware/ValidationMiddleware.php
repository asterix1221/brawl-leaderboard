<?php
namespace App\Infrastructure\Middleware;

use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class ValidationMiddleware {
    public function handle(Request $request, array $rules = []): ?ErrorResponse {
        if (empty($rules)) {
            return null; // No validation rules
        }

        $body = $request->getBody();
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $body[$field] ?? null;

            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[] = "Field '{$field}' is required";
            }

            if (strpos($rule, 'email') !== false && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Field '{$field}' must be a valid email";
            }

            if (preg_match('/min:(\d+)/', $rule, $matches) && $value && strlen($value) < (int)$matches[1]) {
                $errors[] = "Field '{$field}' must be at least {$matches[1]} characters";
            }
        }

        if (!empty($errors)) {
            return new ErrorResponse(implode(', ', $errors), 400);
        }

        return null; // Validation passed
    }
}

