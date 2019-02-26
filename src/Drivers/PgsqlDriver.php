<?php declare(strict_types=1);

namespace Dbl\Drivers;

use Dbl\Collection;
use Dbl\Column;

class PgsqlDriver extends Driver
{
    /**
     * @var array
     */
    protected $typesMap = [
        'character varying' => 'varchar',
        'character' => 'char',
        'timestamp without time zone' => 'datetime',
    ];

    /**
     * @param string $prefix
     *
     * @return Collection
     */
    public function getColumns(string $prefix = ''): Collection
    {
        $query = <<<'SQL'
            SELECT
                *
            FROM
                information_schema.columns
            WHERE TRUE
                AND table_schema = :schema
                AND table_name = :table
SQL;

        $columns = $this->db->fetchAll($query, [
            ':schema' => $this->table->schema,
            ':table' => $this->table->table
        ]);

        return $columns->map(function(int $k, object $v) use ($prefix): array {
            return [$prefix . $v->column_name, new Column(
                $prefix . $v->column_name,
                $this->typesMap[$v->data_type] ?? $v->data_type,
                $v->is_nullable === 'YES' ? true : false,
                $v->character_maximum_length
            )];
        });
    }

    /**
     * @return string
     */
    public function getTableName(): string {
        return sprintf(
            '%s.%s',
            $this->table->schema,
            $this->table->table
        );
    }
}
