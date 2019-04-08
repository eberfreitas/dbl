<?php declare(strict_types=1);

namespace Dbl\Caster;

use Dbl\Column;

interface Caster
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
