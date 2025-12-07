<?php
namespace App\Framework\Container;

class DIContainer {
    private array $bindings = [];
    private array $singletons = [];

    public function set(string $id, callable $factory): void {
        $this->bindings[$id] = $factory;
    }

    public function get(string $id) {
        // Check if already instantiated as singleton
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        // Check if binding exists
        if (!isset($this->bindings[$id])) {
            throw new \RuntimeException("Service not found: {$id}");
        }

        // Create instance
        $instance = $this->bindings[$id]($this);

        // Store as singleton
        $this->singletons[$id] = $instance;

        return $instance;
    }

    public function has(string $id): bool {
        return isset($this->bindings[$id]);
    }
}

