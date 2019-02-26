<?php declare(strict_types=1);

namespace Dbl\Drivers;

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
     * @param string $prefix
     *
     * @return \Dbl\Collection
     */
    abstract public function getColumns(string $prefix = '');

    /**
     * @return string
     */
    abstract public function getTableName();
}
