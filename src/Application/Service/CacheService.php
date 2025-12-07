<?php
namespace App\Application\Service;

class CacheService {
    public function __construct(private \Redis $redis) {}

    public function get(string $key): ?string {
        $value = $this->redis->get($key);
        return $value !== false ? $value : null;
    }

    public function set(string $key, string $value, int $ttl = 300): bool {
        return $this->redis->setex($key, $ttl, $value);
    }

    public function delete(string $key): bool {
        return (bool)$this->redis->del($key);
    }

    public function flush(): bool {
        return $this->redis->flushAll();
    }

    public function has(string $key): bool {
        return (bool)$this->redis->exists($key);
    }
}

