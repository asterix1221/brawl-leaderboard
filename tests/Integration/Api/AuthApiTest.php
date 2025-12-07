<?php
namespace Tests\Integration\Api;

use PHPUnit\Framework\TestCase;

/**
 * Integration тесты для Auth API endpoints
 * 
 * Эти тесты требуют запущенного Docker окружения
 * Запуск: docker-compose up -d && composer test
 */
class AuthApiTest extends TestCase {
    private string $baseUrl = 'http://localhost/api';

    /**
     * @group integration
     */
    public function testRegisterEndpointReturnsSuccess(): void {
        // Этот тест требует запущенного сервера
        // Пропускаем если сервер недоступен
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $email = 'test_' . time() . '@example.com';
        $data = [
            'email' => $email,
            'password' => 'StrongPass123!',
            'nickname' => 'TestPlayer'
        ];

        $response = $this->makeRequest('POST', '/auth/register', $data);

        $this->assertArrayHasKey('success', $response);
        if ($response['success']) {
            $this->assertArrayHasKey('accessToken', $response);
            $this->assertArrayHasKey('user', $response);
        }
    }

    /**
     * @group integration
     */
    public function testLoginEndpointReturnsSuccess(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        // Сначала регистрируем пользователя
        $email = 'login_test_' . time() . '@example.com';
        $password = 'StrongPass123!';
        
        $this->makeRequest('POST', '/auth/register', [
            'email' => $email,
            'password' => $password,
            'nickname' => 'LoginTestPlayer'
        ]);

        // Затем пробуем войти
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        $this->assertArrayHasKey('success', $response);
    }

    /**
     * @group integration
     */
    public function testLoginWithInvalidCredentialsReturnsError(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
    }

    /**
     * @group integration
     */
    public function testRegisterWithDuplicateEmailReturnsError(): void {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('API server is not available');
        }

        $email = 'duplicate_' . time() . '@example.com';
        $data = [
            'email' => $email,
            'password' => 'StrongPass123!',
            'nickname' => 'DuplicateTest'
        ];

        // Первая регистрация
        $this->makeRequest('POST', '/auth/register', $data);

        // Вторая регистрация с тем же email
        $response = $this->makeRequest('POST', '/auth/register', $data);

        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
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

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? ['error' => 'Invalid JSON response'];
    }
}
