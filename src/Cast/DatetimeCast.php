<?php declare(strict_types=1);

namespace Dbl\Cast;

use DateTime;
use Dbl\Column;

class DatetimeCast implements Cast
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return DateTime
     */
    public static function code($value, Column $column): ?DateTime
    {
        if ($value) {
            return new DateTime($value);
        }

        return null;
    }

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return string
     */
    public static function database($value, Column $column): string
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
