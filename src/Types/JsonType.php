<?php declare(strict_types=1);

namespace Dbl\Types;

class JsonType implements Type
{
    /**
     * @param mixed $value
     *
     * @return array
     */
    public static function code($value): array
    {
        if (!is_array($value) && is_string($value)) {
            $value = json_decode($value, true);
        }

        return (array) $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function database($value): string
    {
        if (!is_string($value)) {
            $value = json_encode($value);
        }

        return (string) $value;
    }
}
