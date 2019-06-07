<?php

declare(strict_types=1);

namespace Dbl;

class Cache
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @param string $key
     * @param callable $callback
     *
     * @return mixed
     */
    public function remember(string $key, callable $callback)
    {
        $value = $this->cache[$key] ?? $callback();
        $this->cache[$key] = $value;

        return $value;
    }
}