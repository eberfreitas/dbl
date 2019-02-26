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
        $value = (string) $value;

        if (self::isUnixTimestamp($value)) {
            return (new \DateTime())->setTimestamp((int) $value);
        }

        return new \DateTime($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function database($value): string
    {
        $format = Database::getInstance()->getSettings('date_time_format', 'Y-m-d H:i:s');

        if ($value instanceof \DateTime) {
            return $value->format($format);
        }

        return (string) $value;
    }

    /**
     * @param string $timestamp
     *
     * @return bool
     */
    protected static function isUnixTimestamp(string $timestamp): bool
    {
        // https://stackoverflow.com/a/2524761

        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
