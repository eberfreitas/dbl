<?php

declare(strict_types=1);

namespace Dbl;

abstract class Cache
{
    /**
     * @var array
     */
    private $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     *
     * @return mixed
     */
    abstract public function remember(string $key, int $ttl, callable $callback);
}