<?php
namespace App\Framework\HTTP;

class Response {
    protected int $statusCode;
    protected array $headers = [];

    public function __construct(int $statusCode = 200) {
        $this->statusCode = $statusCode;
    }

    public function setHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }

    public function send(): void {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }
}

