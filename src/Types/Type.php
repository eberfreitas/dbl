<?php declare(strict_types=1);

namespace Dbl\Types;

interface Type
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function code($value);

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function database($value);
}
