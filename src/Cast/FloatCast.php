<?php declare(strict_types=1);

namespace Dbl\Cast;

use Dbl\Column;

class FloatCast implements Cast
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return float
     */
    public static function code($value, Column $column): float
    {
        $precision = $column->raw['numeric_precision'] ?? null;

        if (!is_null($precision)) {
            $value = number_format($value, (int) $precision, '.', '');
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
