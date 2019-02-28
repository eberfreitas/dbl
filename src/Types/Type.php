<?php declare(strict_types=1);

namespace Dbl\Types;

use Dbl\Column;

interface Type
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return mixed
     */
    public static function code($value, Column $column);

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return mixed
     */
    public static function database($value, Column $column);
}
