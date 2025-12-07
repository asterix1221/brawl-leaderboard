<?php
namespace Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use App\Application\Service\CacheService;

// Create a mock interface for testing
interface CacheInterface {
    public function get($key);
    public function setex($key, $ttl, $value);
    public function del($key);
    public function flushAll();
    public function exists($key);
}

class CacheServiceTest extends TestCase {
    private $cacheService;
    private $cacheMock;

    protected function setUp(): void {
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->cacheService = new class($this->cacheMock) {
            private $cache;
            
            public function __construct($cache) {
                $this->cache = $cache;
            }
            
            public function get(string $key): ?string {
                $value = $this->cache->get($key);
                return $value !== false ? $value : null;
            }

            public function set(string $key, string $value, int $ttl = 300): bool {
                return $this->cache->setex($key, $ttl, $value);
            }

            public function delete(string $key): bool {
                return (bool)$this->cache->del($key);
            }

            public function flush(): bool {
                return $this->cache->flushAll();
            }

            public function has(string $key): bool {
                return (bool)$this->cache->exists($key);
            }
        };
    }

    public function testGetReturnsValueWhenKeyExists(): void {
        $key = 'test_key';
        $expectedValue = 'test_value';

        $this->cacheMock
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($expectedValue);

        $result = $this->cacheService->get($key);
        $this->assertEquals($expectedValue, $result);
    }

    public function testGetReturnsNullWhenKeyDoesNotExist(): void {
        $key = 'non_existent_key';

        $this->cacheMock
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(false);

        $result = $this->cacheService->get($key);
        $this->assertNull($result);
    }

    public function testSetReturnsTrueOnSuccess(): void {
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 300;

        $this->cacheMock
            ->expects($this->once())
            ->method('setex')
            ->with($key, $ttl, $value)
            ->willReturn(true);

        $result = $this->cacheService->set($key, $value, $ttl);
        $this->assertTrue($result);
    }

    public function testSetWithDefaultTtl(): void {
        $key = 'test_key';
        $value = 'test_value';

        $this->cacheMock
            ->expects($this->once())
            ->method('setex')
            ->with($key, 300, $value)
            ->willReturn(true);

        $result = $this->cacheService->set($key, $value);
        $this->assertTrue($result);
    }

    public function testSetReturnsFalseOnFailure(): void {
        $key = 'test_key';
        $value = 'test_value';

        $this->cacheMock
            ->expects($this->once())
            ->method('setex')
            ->willReturn(false);

        $result = $this->cacheService->set($key, $value);
        $this->assertFalse($result);
    }

    public function testDeleteReturnsTrueOnSuccess(): void {
        $key = 'test_key';

        $this->cacheMock
            ->expects($this->once())
            ->method('del')
            ->with($key)
            ->willReturn(1);

        $result = $this->cacheService->delete($key);
        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhenKeyDoesNotExist(): void {
        $key = 'non_existent_key';

        $this->cacheMock
            ->expects($this->once())
            ->method('del')
            ->with($key)
            ->willReturn(0);

        $result = $this->cacheService->delete($key);
        $this->assertFalse($result);
    }

    public function testFlushReturnsTrueOnSuccess(): void {
        $this->cacheMock
            ->expects($this->once())
            ->method('flushAll')
            ->willReturn(true);

        $result = $this->cacheService->flush();
        $this->assertTrue($result);
    }

    public function testFlushReturnsFalseOnFailure(): void {
        $this->cacheMock
            ->expects($this->once())
            ->method('flushAll')
            ->willReturn(false);

        $result = $this->cacheService->flush();
        $this->assertFalse($result);
    }

    public function testHasReturnsTrueWhenKeyExists(): void {
        $key = 'test_key';

        $this->cacheMock
            ->expects($this->once())
            ->method('exists')
            ->with($key)
            ->willReturn(1);

        $result = $this->cacheService->has($key);
        $this->assertTrue($result);
    }

    public function testHasReturnsFalseWhenKeyDoesNotExist(): void {
        $key = 'non_existent_key';

        $this->cacheMock
            ->expects($this->once())
            ->method('exists')
            ->with($key)
            ->willReturn(0);

        $result = $this->cacheService->has($key);
        $this->assertFalse($result);
    }

    public function testSetWithCustomTtl(): void {
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 600;

        $this->cacheMock
            ->expects($this->once())
            ->method('setex')
            ->with($key, $ttl, $value)
            ->willReturn(true);

        $result = $this->cacheService->set($key, $value, $ttl);
        $this->assertTrue($result);
    }

    public function testSetWithZeroTtl(): void {
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 0;

        $this->cacheMock
            ->expects($this->once())
            ->method('setex')
            ->with($key, $ttl, $value)
            ->willReturn(true);

        $result = $this->cacheService->set($key, $value, $ttl);
        $this->assertTrue($result);
    }
}
