<?php declare(strict_types=1);

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
     * @param string $name
     * @param string $type
     * @param bool $null
     * @param int|null $length
     *
     * @return void
     */
    public function __construct(
        string $name,
        string $type,
        bool $null,
        ?int $length
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->null = $null;
        $this->length = $length;
    }
}
