<?php
namespace App\Application\DTO;

class LeaderboardEntryDTO {
    public function __construct(
        public readonly int $rank,
        public readonly string $playerId,
        public readonly string $nickname,
        public readonly int $totalTrophies,
        public readonly string $region,
        public readonly int $level
    ) {}

    public function toArray(): array {
        return [
            'rank' => $this->rank,
            'playerId' => $this->playerId,
            'nickname' => $this->nickname,
            'totalTrophies' => $this->totalTrophies,
            'region' => $this->region,
            'level' => $this->level
        ];
    }
}

