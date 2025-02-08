<?php
declare(strict_types=1);

namespace App\Functions;

class InMemoryCache
{
    public $cache = [];

    public function get(string $key, callable $callback)
    {
        if(!array_key_exists($key, $this->cache))
            $this->cache[$key] = $callback();

        return $this->cache[$key];
    }
}