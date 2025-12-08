<?php

namespace Tests\Integration\Support;

use App\Application\Service\BrawlStarsService;

class FakeBrawlStarsService extends BrawlStarsService
{
    public function __construct(private array $fixtures = []) {}

    public function getPlayerById(string $playerId): ?array
    {
        return $this->fixtures[$playerId] ?? null;
    }
}
