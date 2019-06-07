<?php

declare(strict_types=1);

namespace Dbl\Driver;

use Dbl\Collection;
use Dbl\Column;
use Dbl\Cast\DatetimeCast;
use Dbl\Cast\IntegerCast;
use Dbl\Exception;

class MysqlDriver extends Driver
{
    /**
     * @var array
     */
    protected $castingMap = [
        'int' => IntegerCast::class,
        'timestamp' => DatetimeCast::class,
    ];

    /**
     * @return Collection
     *
     * @throws Exception
     */
    public function getColumns(): Collection
    {
        $cachableTableName = str_replace([' ', '-', '.'], '_', $this->getTableName());
        $cacheKey = sprintf('__dbl_mysql_%s_columns', $cachableTableName);

        $columnsInfo = $this->db->cache(
            $cacheKey,
            $this->db->settings['cache_settings']['ttl'],
            function (): array {
                $query = 'SHOW COLUMNS FROM ' . $this->getTableName();
                $columns = $this->db->fetchAll($query)->raw();

                foreach ($columns as $k => $v) {
                    $columns[$k] = $v->raw();
                }

                return $columns;
            }
        );

        $columns = new Collection();

        foreach ($columnsInfo as $info) {
            $type = $info['Type'];
            $length = null;

            if (preg_match('/([a-z]+)\((\d+)\)/', $type, $matches)) {
                $type = $matches[1];
                $length = (int) $matches[2];
            }

            $columns[] = new Column(
                $info['Field'],
                $type,
                $info['Null'] === 'YES' ? true : false,
                $length,
                $info
            );
        }

        return $columns;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table->table;
    }
}
