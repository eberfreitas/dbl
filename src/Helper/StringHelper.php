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

    /**
     * @param string $search
     * @param string $replace
     * @param string $str
     *
     * @link https://pageconfig.com/post/replace-last-occurrence-of-a-string-php Original source
     *
     * @return string
     */
    static public function stringReplaceLast(string $search, string $replace, string $str): string
    {
        if (($pos = strrpos($str, $search)) !== false) {
            $search_length = strlen($search);
            $str = substr_replace($str, $replace, $pos, $search_length);
        }

        return $str;
    }
}
