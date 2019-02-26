<?php declare(strict_types=1);

namespace Dbl\Types;

class IntegerType implements Type
{
    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function code($value): int
    {
        return (int) $value;
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function database($value): int
    {
        return (int) $value;
    }
}
