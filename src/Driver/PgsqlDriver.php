<?php declare(strict_types=1);

namespace Dbl\Driver;

use Dbl\Collection;
use Dbl\Column;

class PgsqlDriver extends Driver
{
    /**
     * @var array
     */
    protected $castingMap = [
        'smallint' => 'integer',
        'integer' => 'integer',
        'bigint' => 'integer',
        'decimal' => 'float',
        'numeric' => 'float',
        'real' => 'float',
        'double precision' => 'float',
        'smallserial' => 'integer',
        'serial' => 'integer',
        'bigserial' => 'integer',
    ];

    /**
     * @return Collection
     */
    public function getColumns(): Collection
    {
        $cachableTableName = str_replace([' ', '-', '.'], '_', $this->getTableName());
        $cacheKey = sprintf('__dbl_pgsql_%s_columns', $cachableTableName);

        $columnsInfo = $this->db->cache(
            $cacheKey,
            $this->db->settings['cache_ttl'],
            function (): array {
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
                ])->raw();

                foreach ($columns as $k => $v) {
                    $columns[$k] = $v->raw();
                }

                return $columns;
            }
        );

        $columns = new Collection();

        foreach ($columnsInfo as $info) {
            $dataType = $info['data_type'] === 'USER-DEFINED'
                ? $info['udt_name']
                : $info['data_type'];

            $columns[] = new Column(
                $info['column_name'],
                $dataType,
                $info['is_nullable'] === 'YES' ? true: false,
                $info['character_maximum_length'],
                $info
            );
        }

        return $columns;
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
