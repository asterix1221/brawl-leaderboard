<?php
namespace Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use App\Application\Service\JWTService;

class JWTServiceTest extends TestCase {
    private JWTService $jwtService;
    private string $testSecret = 'test-secret-key-for-jwt-testing';

    protected function setUp(): void {
        $this->jwtService = new JWTService($this->testSecret);
    }

    public function testGenerateAccessToken(): void {
        $payload = ['userId' => 'user123', 'email' => 'test@example.com'];
        $token = $this->jwtService->generateAccessToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token); // JWT has 3 parts separated by dots
    }

    public function testGenerateRefreshToken(): void {
        $payload = ['userId' => 'user123'];
        $token = $this->jwtService->generateRefreshToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token);
    }

    public function testVerifyValidToken(): void {
        $payload = ['userId' => 'user123', 'email' => 'test@example.com'];
        $token = $this->jwtService->generateAccessToken($payload);

        $decoded = $this->jwtService->verifyToken($token);

        $this->assertIsArray($decoded);
        $this->assertEquals('user123', $decoded['userId']);
        $this->assertEquals('test@example.com', $decoded['email']);
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertArrayHasKey('exp', $decoded);
    }

    public function testVerifyInvalidToken(): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->jwtService->verifyToken('invalid.token.here');
    }

    public function testVerifyEmptyToken(): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->jwtService->verifyToken('');
    }

    public function testTokenIsNotExpired(): void {
        $payload = ['userId' => 'user123'];
        $token = $this->jwtService->generateAccessToken($payload);
        $decoded = $this->jwtService->verifyToken($token);

        $this->assertFalse($this->jwtService->isTokenExpired($decoded));
    }

    public function testTokenExpired(): void {
        // Create a token that's already expired
        $past = time() - 7200; // 2 hours ago
        $payload = [
            'userId' => 'user123',
            'iat' => $past - 3600, // issued 3 hours ago
            'exp' => $past // expired 2 hours ago
        ];

        // Create token manually with expired timestamp
        $token = \Firebase\JWT\JWT::encode($payload, $this->testSecret, 'HS256');
        
        $this->expectException(\RuntimeException::class);
        $this->jwtService->verifyToken($token);
    }

    public function testDifferentTokensForSamePayload(): void {
        $payload1 = ['userId' => 'user123'];
        $payload2 = ['userId' => 'user123', 'extra' => 'data'];
        
        $token1 = $this->jwtService->generateAccessToken($payload1);
        $token2 = $this->jwtService->generateAccessToken($payload2);

        // Tokens should be different because they have different payloads
        $this->assertNotEquals($token1, $token2);
    }

    public function testAccessTokenAndRefreshTokenAreDifferent(): void {
        $payload = ['userId' => 'user123'];
        
        $accessToken = $this->jwtService->generateAccessToken($payload);
        $refreshToken = $this->jwtService->generateRefreshToken($payload);

        $this->assertNotEquals($accessToken, $refreshToken);
    }

    public function testTokenWithComplexPayload(): void {
        $payload = [
            'userId' => 'user123',
            'email' => 'test@example.com',
            'nickname' => 'TestUser',
            'roles' => ['user', 'admin']
        ];

        $token = $this->jwtService->generateAccessToken($payload);
        $decoded = $this->jwtService->verifyToken($token);

        $this->assertEquals($payload['userId'], $decoded['userId']);
        $this->assertEquals($payload['email'], $decoded['email']);
        $this->assertEquals($payload['nickname'], $decoded['nickname']);
        $this->assertEquals($payload['roles'], $decoded['roles']);
    }

    public function testConstructorWithEmptySecret(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JWT secret cannot be empty');

        new JWTService('');
    }

    public function testConstructorWithNullSecret(): void {
        $this->expectException(\TypeError::class);

        new JWTService(null);
    }
}
