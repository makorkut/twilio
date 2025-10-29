<?php

declare(strict_types=1);

namespace App\Core;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    public function get(string $abstract)
    {
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $instance = $this->bindings[$abstract]($this);

            if (array_key_exists($abstract, $this->instances)) {
                $this->instances[$abstract] = $instance;
            }

            return $instance;
        }

        throw new \Exception("No binding found for {$abstract}");
    }
}
