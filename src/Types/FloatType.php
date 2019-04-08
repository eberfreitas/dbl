<?php declare(strict_types=1);

namespace Dbl\Types;

use Dbl\Column;

class FloatType implements Type
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return float
     */
    public static function code($value, Column $column): float
    {
        $precision = (int) $column->raw['numeric_precision'] ?? null;

        if (!is_null($precision)) {
            $value = number_format($value, $precision);
        }

        return (float) $value;
    }

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return float
     */
    public static function database($value, Column $column): float
    {
        return self::code($value, $column);
    }
}
