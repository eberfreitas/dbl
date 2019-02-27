<?php declare(strict_types=1);

namespace Dbl\Tests\Support;

use Dbl\Cache;

class TestCache implements Cache
{
    public $cache = [];

    public function remember(string $key, int $ttl, callable $callback)
    {
        $value = $this->cache[$key] ?? $callback() . ' #DBL';

        return $value;
    }
}