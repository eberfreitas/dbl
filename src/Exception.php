<?php

namespace Dbl;

use Dbl\Traits\MagicGetTrait;

/**
 * @property-read string $query
 * @property-read array $params
 */
class Exception extends \Exception
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
     * @param string $message
     * @param string $query
     * @param array $params
     */
    public function __construct(string $message = '', string $query = '', array $params = [])
    {
        $this->query = $query;
        $this->params = $params;

        return parent::__construct($message);
    }
}
