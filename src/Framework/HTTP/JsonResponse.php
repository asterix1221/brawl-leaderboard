<?php
namespace App\Framework\HTTP;

class JsonResponse extends Response {
    private array $data;

    public function __construct(array $data, int $statusCode = 200) {
        parent::__construct($statusCode);
        $this->data = $data;
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
    }

    public function __toString(): string {
        $this->send();
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

