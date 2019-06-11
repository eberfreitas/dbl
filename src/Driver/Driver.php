<?php

declare(strict_types=1);

namespace Dbl\Driver;

use Dbl\Collection;
use Dbl\Column;
use Dbl\Exception\Exception;
use Dbl\Table;
use Dbl\Database;

abstract class Driver
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @param Table $table
     *
     * @throws Exception
     *
     * @return void
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return Collection
     */
    abstract public function getColumns(): Collection;

    /**
     * @return string
     */
    abstract public function getTableName(): string;

    /**
     * @param Column $column
     *
     * @return string|null
     */
    public function getCaster(Column $column): ?string
    {
        return $this->castingMap[$column->type] ?? null;
    }
}
