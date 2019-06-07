<?php

declare(strict_types=1);

namespace Dbl\Helper;

class StringHelper
{
    /**
     * @param string $string
     *
     * @return string
     */
    static function camelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['.', '_', '-'], ' ', $string)));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    static public function snakeCase(string $string): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_');
    }
}
