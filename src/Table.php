<?php declare(strict_types=1);

namespace Dbl;

use Dbl\Drivers\Driver;
use Dbl\Exception;
use Dbl\Traits\ObjectMagicGetTrait;
use Dbl\Types\{BooleanType, DatetimeType, IntegerType, JsonType};

abstract class Table
{
    use ObjectMagicGetTrait;

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
     * @var Driver
     */
    protected $driver;

    /**
     * @var \Dbl\Collection
     */
    protected $columns;

    /**
     * @var string
     */
    protected $connection = 'default';

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var array
     */
    protected $typesMap = [
        'integer' => IntegerType::class,
        'json' => JsonType::class,
        'boolean' => BooleanType::class,
        'datetime' => DatetimeType::class,
    ];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->driver = $this->driverFactory($this->getDriverName());
        $this->columns = $this->driver->getColumns();

        if (!empty($this->db->getSettings('types_map'))) {
            $this->typesMap = array_merge($this->typesMap, $this->db->getSettings('types_map'));
        }
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
        $class = sprintf('\\Dbl\\Drivers\\%sDriver', ucfirst($driver));

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
            $column = $columns[$k] ?? null;

            if (is_null($column)) {
                continue;
            }

            $type = $column->type;
            $typeClass = $this->typesMap[$type] ?? null;

            if (is_null($typeClass)) {
                continue;
            }

            $data[$k] = call_user_func([$typeClass, $target], $v);
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
        $columns = array_keys($this->columns->raw());
        $save = [];
        $now = (new \DateTime)->format(
            $this->db->getSettings('date_time_format', 'Y-m-d H:i:s')
        );

        $data->created = $now;
        $data->modified = $now;

        foreach ($data as $k => $v) {
            if (in_array($k, $columns)) {
                $save[$k] = $v;
            }
        }

        $save = $this->castToDatabase($save);
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
        $columns = array_keys($this->columns->raw());
        $save = [];
        $data->modified = (new \DateTime)->format(
            $this->db->getSettings('date_time_format', 'Y-m-d H:i:s')
        );

        unset($data['created']);

        if (!in_array($this->primaryKey, array_keys($data->raw()))) {
            throw new Exception(sprintf('Can\'t update a record without primary key ("%s")', $this->primaryKey));
        }

        $primaryKeyValue = $data[$this->primaryKey];

        unset($data[$this->primaryKey]);

        foreach ($data as $k => $v) {
            if (in_array($k, $columns) && $data->isDirty($k)) {
                $save[$k] = $v;
            }
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
                return sprintf('%s = ?', $column);
            }, array_keys($conditions)))
        );

        return (int) $this->db->single($query, $params, $this->connection);
    }
}
