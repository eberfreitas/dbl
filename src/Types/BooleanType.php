<?php declare(strict_types=1);

namespace Dbl\Types;

class BooleanType implements Type
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function code($value): bool
    {
        return (bool) $value;
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function database($value): int
    {
        return (int) boolval($value);
    }
}
