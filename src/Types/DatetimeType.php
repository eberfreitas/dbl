<?php declare(strict_types=1);

namespace Dbl\Types;

use Dbl\Database;

class DatetimeType implements Type
{
    /**
     * @param mixed $value
     *
     * @return \DateTime
     */
    public static function code($value): \DateTime
    {
        return new \DateTime((string) $value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function database($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
