<?php
namespace Tests\Integration\Api;

use PHPUnit\Framework\TestCase;

/**
 * Integration тесты для Leaderboard API endpoints
 */
class LeaderboardApiTest extends TestCase {
    private string $baseUrl = 'http://localhost/api';

    /**
     * @group integration
     */
    public function testGetGlobalLeaderboardReturnsData(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $response = $this->makeRequest('GET', '/leaderboards/global');

        $this->assertArrayHasKey('success', $response);
        if ($response['success']) {
            $this->assertArrayHasKey('data', $response);
            $this->assertIsArray($response['data']['players'] ?? []);
            $this->assertArrayHasKey('pagination', $response['data']);
        }
    }

    /**
     * @group integration
     */
    public function testGetGlobalLeaderboardWithParameters(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $response = $this->makeRequest('GET', '/leaderboards/global?limit=5&offset=10');

        $this->assertArrayHasKey('success', $response);
        if ($response['success']) {
            $this->assertArrayHasKey('data', $response);
            $data = $response['data'];
            $this->assertArrayHasKey('players', $data);
            $this->assertLessThanOrEqual(5, count($data['players']));
        }
    }

    /**
     * @group integration
     */
    public function testSearchPlayersReturnsResults(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $response = $this->makeRequest('GET', '/players/search?q=Test&limit=10');

        $this->assertArrayHasKey('success', $response);
        if ($response['success']) {
            $this->assertArrayHasKey('data', $response);
            $this->assertIsArray($response['data']['players'] ?? []);
        }
    }

    /**
     * @group integration
     */
    public function testHealthEndpointReturnsStatus(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $response = $this->makeRequest('GET', '/health');

        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('ok', $response['status']);
    }

    private function isServerAvailable(): bool {
        $ch = curl_init($this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        if ($method === 'GET' && !empty($data)) {
            $queryString = http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint . '?' . $queryString);
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? ['error' => 'Invalid JSON response'];
    }
}
