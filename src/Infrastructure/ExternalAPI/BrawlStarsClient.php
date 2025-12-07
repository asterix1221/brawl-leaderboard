<?php
namespace App\Infrastructure\ExternalAPI;

use App\Application\Service\BrawlStarsService;

// Alias для совместимости
class BrawlStarsClient extends BrawlStarsService {
    public function __construct(string $apiKey) {
        parent::__construct($apiKey);
    }
}

