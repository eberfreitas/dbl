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
     * @return bool|null
     */
    public static function code($value, Column $column): ?bool
    {
        if (is_null($value)) {
            return null;
        }

        return (bool) boolval($value);
    }

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return int|null
     */
    public static function database($value, Column $column): ?int
    {
        if (is_null($value) && $column->null) {
            return null;
        }

        return (int) boolval($value);
    }
}
