<?php declare(strict_types=1);

namespace Dbl\Tests\Support;

use Dbl\Cache;

class TestCache extends Cache
{
    public $cache = [];

    public function remember(string $key, callable $callback)
    {
        $value = $this->cache[$key] ?? $callback() . ' #DBL';

        return $value;
    }
}