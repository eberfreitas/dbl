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
     * @var mixed|null
     */
    public $extra;

    /**
     * @param string $name
     * @param string $type
     * @param bool $null
     * @param int|null $length
     * @param mixed|null $extra
     *
     * @return void
     */
    public function __construct(
        string $name,
        string $type,
        bool $null,
        ?int $length,
        $extra = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->null = $null;
        $this->length = $length;
        $this->extra = $extra;
    }
}
