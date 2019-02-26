<?php declare(strict_types=1);

namespace Dbl\Traits;

trait ObjectMagicGetTrait
{
    /**
     * @param string $param
     *
     * @return mixed
     */
    public function __get(string $param)
    {
        if (isset($this->{$param})) {
            return $this->{$param};
        }

        return null;
    }
}
