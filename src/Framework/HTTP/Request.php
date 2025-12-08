<?php
namespace App\Framework\HTTP;

class Request {
    private array $query;
    private array $body;
    private array $headers;
    private array $attributes = [];

    public function __construct() {
        $this->query = $_GET ?? [];
        $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->headers = getallheaders() ?? [];
    }

    public static function fromGlobals(): self {
        return new self();
    }

    public function getQuery(string $key, $default = null) {
        return $this->query[$key] ?? $default;
    }

    public function getBody(?string $key = null, $default = null) {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function getHeader(string $name): ?string {
        $name = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        return null;
    }

    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function setAttribute(string $key, $value): void {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, $default = null) {
        return $this->attributes[$key] ?? $default;
    }
}

