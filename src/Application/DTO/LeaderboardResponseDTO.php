<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO для ответа лидерборда
 */
final class LeaderboardResponseDTO
{
    /**
     * @param array<int, array{id: string, nickname: string, trophies: int, rank: int, level: int, region: string}> $players
     */
    public function __construct(
        public readonly array $players,
        public readonly int $total = 0,
        public readonly int $page = 1,
        public readonly int $perPage = 50
    ) {
    }

    /**
     * Преобразовать в массив
     */
    public function toArray(): array
    {
        return [
            'success' => true,
            'data' => [
                'players' => $this->players,
                'pagination' => [
                    'total' => $this->total,
                    'page' => $this->page,
                    'perPage' => $this->perPage,
                    'totalPages' => $this->perPage > 0 ? (int) ceil($this->total / $this->perPage) : 0,
                ],
            ],
        ];
    }
}
