<?php declare(strict_types=1);

namespace Dbl;

interface Cache
{
    /**
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     *
     * @return mixed
     */
    public function remember(string $key, int $ttl, callable $callback);
}