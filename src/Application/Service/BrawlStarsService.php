<?php
namespace App\Application\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BrawlStarsService {
    private Client $client;
    private string $apiKey;
    private string $baseUrl = 'https://api.brawlstars.com/v1';

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json'
            ],
            'timeout' => 10
        ]);
    }

    public function getTopPlayers(int $limit = 100): array {
        try {
            $response = $this->client->get('/rankings/global/players', [
                'query' => ['limit' => $limit]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['items'] ?? [];
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to fetch top players: ' . $e->getMessage());
        }
    }

    public function getPlayerById(string $playerId): ?array {
        try {
            $response = $this->client->get('/players/' . urlencode($playerId));
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw new \RuntimeException('Failed to fetch player: ' . $e->getMessage());
        }
    }

    public function getClubs(string $clubId): ?array {
        try {
            $response = $this->client->get('/clubs/' . urlencode($clubId));
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw new \RuntimeException('Failed to fetch club: ' . $e->getMessage());
        }
    }
}

