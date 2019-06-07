<?php

declare(strict_types=1);

namespace Dbl;

class Column
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $null;

    /**
     * @var int|null
     */
    public $length;

    /**
     * @var array
     */
    public $raw;

    /**
     * @param string $name
     * @param string $type
     * @param bool $null
     * @param int|null $length
     * @param array $raw
     *
     * @return void
     */
    public function __construct(
        string $name,
        string $type,
        bool $null,
        ?int $length,
        array $raw
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->null = $null;
        $this->length = $length;
        $this->raw = $raw;
    }
}
