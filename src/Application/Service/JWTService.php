<?php
namespace App\Application\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService {
    private string $secret;
    private string $algorithm = 'HS256';
    private int $accessTokenExpiry = 3600; // 1 hour
    private int $refreshTokenExpiry = 604800; // 7 days

    public function __construct(string $secret) {
        if (empty($secret)) {
            throw new \InvalidArgumentException('JWT secret cannot be empty');
        }
        $this->secret = $secret;
    }

    public function generateAccessToken(array $payload): string {
        return $this->generateToken($payload, $this->accessTokenExpiry);
    }

    public function generateRefreshToken(array $payload): string {
        return $this->generateToken($payload, $this->refreshTokenExpiry);
    }

    private function generateToken(array $payload, int $expiry): string {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $expiry;

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function verifyToken(string $token): array {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array)$decoded;
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid token: ' . $e->getMessage());
        }
    }

    public function isTokenExpired(array $token): bool {
        return isset($token['exp']) && $token['exp'] < time();
    }
}

