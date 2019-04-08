<?php declare(strict_types=1);

namespace Dbl\Caster;

use Dbl\Column;

class JsonCaster implements Caster
{
    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return array
     */
    public static function code($value, Column $column): array
    {
        if (!is_array($value) && is_string($value)) {
            $value = json_decode($value, true);
        }

        return (array) $value;
    }

    /**
     * @param mixed $value
     * @param Column $column
     *
     * @return string
     */
    public static function database($value, Column $column): string
    {
        if (!is_string($value)) {
            $value = json_encode($value);
        }

        return (string) $value;
    }
}
