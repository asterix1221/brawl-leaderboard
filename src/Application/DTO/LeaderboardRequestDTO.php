<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO для запроса лидерборда
 */
final class LeaderboardRequestDTO
{
    public function __construct(
        public readonly ?string $region = null,
        public readonly ?int $limit = 50,
        public readonly ?int $offset = 0
    ) {
    }

    /**
     * Создать из массива
     */
    public static function fromArray(array $data): self
    {
        return new self(
            region: $data['region'] ?? null,
            limit: isset($data['limit']) ? (int) $data['limit'] : 50,
            offset: isset($data['offset']) ? (int) $data['offset'] : 0
        );
    }

    /**
     * Преобразовать в массив
     */
    public function toArray(): array
    {
        return [
            'region' => $this->region,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
