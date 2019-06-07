<?php

declare(strict_types=1);

namespace Dbl\Cast;

use Dbl\Column;

class BooleanCast implements Cast
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return bool
     */
    public static function code($value, Column $column): bool
    {
        return (bool) boolval($value);
    }

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return int
     */
    public static function database($value, Column $column): int
    {
        return (int) boolval($value);
    }
}
