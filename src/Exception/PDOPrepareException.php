<?php

declare(strict_types=1);

namespace Dbl\Exception;

use Dbl\Helper\MagicGetTrait;
use Exception;

class PDOPrepareException extends Exception
{
    use MagicGetTrait;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $params;

    /**
     * @param string $query
     * @param array $params
     */
    public function __construct(string $query = '', array $params = [])
    {
        $this->query = $query;
        $this->params = $params;

        parent::__construct('Error while preparing statement.');
    }
}