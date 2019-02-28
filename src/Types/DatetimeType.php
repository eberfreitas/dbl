<?php declare(strict_types=1);

namespace Dbl\Types;

use Dbl\Column;

class DatetimeType implements Type
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return \DateTime
     */
    public static function code($value, Column $column): \DateTime
    {
        return new \DateTime((string) $value);
    }

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return string
     */
    public static function database($value, Column $column): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
