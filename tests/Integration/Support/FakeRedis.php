<?php

namespace {
    if (!class_exists('Redis')) {
        class Redis {}
    }
}

namespace Tests\Integration\Support {
    class FakeRedis extends \Redis
    {
        private array $storage = [];

        public function get(string $key): string|false
        {
            return $this->storage[$key] ?? false;
        }

        public function setex(string $key, int $ttl, string $value): bool
        {
            $this->storage[$key] = $value;
            return true;
        }

        public function del(string $key): int
        {
            if (isset($this->storage[$key])) {
                unset($this->storage[$key]);
                return 1;
            }

            return 0;
        }

        public function flushAll(): bool
        {
            $this->storage = [];
            return true;
        }

        public function exists(string $key): int
        {
            return isset($this->storage[$key]) ? 1 : 0;
        }
    }
}
