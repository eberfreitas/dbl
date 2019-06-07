<?php

declare(strict_types=1);

namespace Dbl\Exception;

use Exception;

class MissingDriverException extends Exception
{
    public function __construct(string $driver)
    {
        parent::__construct(sprintf('No driver found for "%s"', $driver));
    }
}
