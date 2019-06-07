<?php

declare(strict_types=1);

namespace Dbl;

use DateTime;
use Dbl\Driver\Driver;
use Dbl\Helper\MagicGetTrait;
use Dbl\Helper\StringHelper as S;

abstract class Table
{
    use MagicGetTrait;

    /**
     * @var string
     */
    protected $schema = 'public';

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $connection = 'default';

    /**
     * @var array
     */
    protected $timestamps = [
        'create' => 'created_at',
        'update' => 'updated_at',
    ];

    /**
     * @var array
     */
    protected $cast = [];

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var Collection
     */
    private $columns;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->driver = $this->driverFactory($this->getDriverName());
        $this->columns = $this->driver->getColumns();
    }

    /**
     * @return string
     */
    protected function getDriverName(): string
    {
        return $this->db->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @param string $driver
     *
     * @throws Exception
     *
     * @return Driver
     */
    protected function driverFactory(string $driver): Driver
    {
        $class = sprintf('\\Dbl\\Driver\\%sDriver', ucfirst($driver));

        if (class_exists($class)) {
            /** @var Driver */
            return new $class($this);
        }

        throw new Exception(sprintf('No driver found for "%s"', $driver));
    }

    /**
     * @param string $target
     * @param array $data
     *
     * @return array
     */
    protected function castTo(string $target, array $data): array
    {
        $columns = $this->columns;

        foreach ($data as $k => $v) {
            $column = $columns->pluck(function ($col) use ($k): bool {
                return $col->name === $k;
            });

            if (is_null($column)) {
                continue;
            }

            $casterClass = $this->cast[$column->name]
                ?? $this->driver->getCaster($column)
                ?? null;

            if (is_null($casterClass)) {
                continue;
            }

            $data[$k] = call_user_func([$casterClass, $target], $v, $column);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function castToCode(array $data): array
    {
        return $this->castTo('code', $data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function castToDatabase(array $data): array
    {
        return $this->castTo('database', $data);
    }

    /**
     * @param Record $data
     *
     * @return Summary
     */
    public function save(Record $data): Summary
    {
        $columns = $this->columns->map(function ($k, $v): string {
            return $v->name;
        });

        $data = $data->filter(function ($k, $v) use ($columns): bool {
            return in_array($k, $columns->raw());
        });

        if ($columns->has($this->timestamps['create'])) {
            $data[$this->timestamps['create']] = new DateTime();
        }

        if ($columns->has($this->timestamps['update'])) {
            unset($data[$this->timestamps['update']]);
        }

        $save = $this->castToDatabase($data->raw());
        $template = 'INSERT INTO %s (%s) VALUES (%s)';
        $query = sprintf(
            $template,
            $this->driver->getTableName(),
            join(', ', array_keys($save)),
            join(', ', array_fill(0, count($save), '?'))
        );

        return $this->db->execute(
            $query,
            array_values($save),
            $this->connection
        );
    }

    /**
     * @param Record $data
     *
     * @throws Exception
     *
     * @return Summary
     */
    public function update(Record $data): Summary
    {
        $columns = $this->columns->map(function ($k, $v): string {
            return $v->name;
        });

        $save = [];

        if (!in_array($this->primaryKey, array_keys($data->raw()))) {
            throw new Exception(sprintf('Can\'t update a record without primary key ("%s")', $this->primaryKey));
        }

        $primaryKeyValue = $data[$this->primaryKey];

        unset($data[$this->primaryKey]);

        foreach ($data as $k => $v) {
            if (in_array($k, $columns->raw()) && $data->isDirty($k)) {
                $save[$k] = $v;
            }
        }

        if ($columns->has($this->timestamps['create'])) {
            unset($save[$this->timestamps['create']]);
        }

        if ($columns->has($this->timestamps['update'])) {
            $save[$this->timestamps['update']] = new DateTime();
        }

        $save = $this->castToDatabase($save);
        $template = 'UPDATE %s SET %s WHERE %s';
        $set = array_map(function(string $column): string {
            return sprintf('%s = ?', $column);
        }, array_keys($save));

        $set = join(', ', $set);
        $where = sprintf('%s = ?', $this->primaryKey);
        $query = sprintf(
            $template,
            $this->driver->getTableName(),
            $set,
            $where
        );

        $params = array_values($save);
        $params[] = $primaryKeyValue;

        return $this->db->execute(
            $query,
            $params,
            $this->connection
        );
    }

    /**
     * @param array $conditions
     *
     * @throws Exception
     *
     * @return int
     */
    public function count(array $conditions): int
    {
        $columns = array_keys($this->columns->raw());
        $conditions = array_filter($conditions, function($v, $k) use ($columns): bool {
            return in_array($k, $columns);
        }, \ARRAY_FILTER_USE_BOTH);

        if (empty($conditions)) {
            throw new Exception('The `$conditions` array keys must match existing columns in this table.');
        }

        $params = array_values($conditions);
        $template = 'SELECT COUNT(%s) FROM %s WHERE %s';
        $query = sprintf(
            $template,
            $this->primaryKey,
            $this->driver->getTableName(),
            join(' AND ', array_map(function (string $column): string {
                $symbols = ['<>', '!='];
                $symbol = '=';

                /** @var string $s */
                foreach ($symbols as $s) {
                    if (strpos($column, $s) !== false) {
                        $symbol = $s;
                        $column = trim(str_replace($s, '', $column));
                        break;
                    }
                }

                return sprintf('%s %s ?', $column, $symbol);
            }, array_keys($conditions)))
        );

        return (int) $this->db->single($query, $params, $this->connection);
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (strpos($method, 'findBy') !== false) {
            $column = S::snakeCase(str_replace('findBy', '', $method));
            $query = sprintf(
                'SELECT * FROM %s WHERE %s = ?',
                $this->driver->getTableName(),
                $column
            );

            return $this->db->fetchAll($query, $args);
        }

        if (strpos($method, 'findFirstBy') !== false) {
            $column = S::snakeCase(str_replace('findFirstBy', '', $method));
            $query = sprintf(
                'SELECT * FROM %s WHERE %s = ? LIMIT 1',
                $this->driver->getTableName(),
                $column
            );

            return $this->db->first($query, $args);
        }
    }
}
