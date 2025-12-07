<?php
namespace App\Application\Service;

class RateLimitService {
    public function __construct(
        private \Redis $redis,
        private int $maxRequests = 100,
        private int $windowSeconds = 60
    ) {}

    public function isAllowed(string $identifier): bool {
        $key = 'rate_limit:' . $identifier;
        $current = $this->redis->get($key);

        if ($current === false) {
            $this->redis->setex($key, $this->windowSeconds, 1);
            return true;
        }

        if ((int)$current >= $this->maxRequests) {
            return false;
        }

        $this->redis->incr($key);
        return true;
    }

    public function increment(string $identifier): void {
        $key = 'rate_limit:' . $identifier;
        $this->redis->incr($key);
        $this->redis->expire($key, $this->windowSeconds);
    }

    public function reset(string $identifier): void {
        $key = 'rate_limit:' . $identifier;
        $this->redis->del($key);
    }

    public function getRemaining(string $identifier): int {
        $key = 'rate_limit:' . $identifier;
        $current = (int)$this->redis->get($key);
        return max(0, $this->maxRequests - $current);
    }
}

