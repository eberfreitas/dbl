<?php declare(strict_types=1);

namespace Dbl\Drivers;

use Dbl\Collection;
use Dbl\Column;
use Dbl\Table;
use Dbl\Database;

abstract class Driver
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @param Table $table
     *
     * @return void
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->db = Database::getInstance();
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
