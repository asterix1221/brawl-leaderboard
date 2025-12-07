<?php
namespace App\Application\DTO;

class PlayerDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $nickname,
        public readonly int $totalTrophies,
        public readonly string $region,
        public readonly int $level
    ) {}

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'totalTrophies' => $this->totalTrophies,
            'region' => $this->region,
            'level' => $this->level
        ];
    }
}

