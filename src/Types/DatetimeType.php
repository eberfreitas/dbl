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
        $format = Database::getInstance()
            ->getSettings('date_time_format', 'Y-m-d H:i:s');

        if ($value instanceof \DateTime) {
            return $value->format($format);
        }

        return (string) $value;
    }
}
