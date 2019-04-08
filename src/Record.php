<?php declare(strict_types=1);

namespace Dbl;

abstract class Record extends Collection
{
    /**
     * @var string
     */
    protected $tableClass = '';

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $raw;

    /**
     * @var array
     */
    protected $dirty = [];

    /**
     * @var Database
     */
    protected $db;

    /**
     * @param array $data
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $table = $this->tableClass;

        if (!class_exists($table)) {
            throw new Exception(sprintf('The table class "%s" doesn\'t exist.', $table));
        }

        /** @var Table */
        $this->table = new $table();
        $this->db = Database::getInstance();
        $this->raw = $data;

        $relatedDataSeparator = $this->db->settings['related_data_separator'];
        $relatedData = [];

        foreach ($data as $k => $v) {
            if (strpos($k, $relatedDataSeparator) !== false) {
                list($table, $key) = explode($relatedDataSeparator, $k);

                if (!isset($relatedData[$table])) {
                    $relatedData[$table] = [];
                }

                $relatedData[$table][$key] = $v;

                unset($data[$k]);

                continue;
            }

            $data[$k] = $this->set($k, $v);
        }

        $data = $this->table->castToCode($data);

        foreach ($relatedData as $table => $d) {
            $data[$table] = $this->relatedDataFactory($table, $d);
        }

        parent::__construct($data);
    }

    /**
     * @param string $table
     * @param array $data
     *
     * @return Collection
     */
    protected function relatedDataFactory(string $table, array $data): Collection
    {
        $class = '\\' . trim(__NAMESPACE__, '\\') . '\\' . $this->camelize($table);

        if (class_exists($class)) {
            /** @var Collection */
            $data = new $class($data);
        } else {
            $data = new Collection($data);
        }

        return $data;
    }

    /**
     * @param string $offset
     *
     * @return void
     */
    protected function makeDirty(string $offset): void
    {
        $value = $this->data[$offset];
        $original = $this->raw[$offset];

        if ($value !== $original && !in_array($offset, $this->dirty)) {
            $this->dirty[] = $offset;
        }
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function isDirty(string $column = ''): bool
    {
        if ($column === '') {
            return !empty($this->dirty);
        }

        return in_array($column, $this->dirty);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function set(string $key, $value)
    {
        $method = 'set' . $this->camelize($key);

        if (method_exists($this, $method)) {
            $value = call_user_func([$this, $method], $value);
        }

        return $value;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function __set($offset, $value): void
    {
        $this->data[$offset] = $this->set($offset, $value);

        $this->makeDirty($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_null($offset)) {
            $this->data[$offset] = $this->set($offset, $value);

            $this->makeDirty($offset);
        }
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function __get($offset)
    {
        $value = isset($this->data[$offset]) ? $this->data[$offset] : null;

        if (is_null($value)) {
            return null;
        }

        $method = 'get' . $this->camelize($offset);

        if (method_exists($this, $method)) {
            $value = call_user_func([$this, $method], $value);
        }

        return $value;
    }

    /**
     * @return Summary
     */
    public function save(): Summary
    {
        $pk = $this->table->primaryKey;

        if (array_key_exists($pk, $this->data)) {
            $summary = $this->table->update($this);
        } else {
            $summary = $this->table->save($this);
        }

        $this->data[$pk] = $summary->lastInsertId;
        $this->raw = $this->data;
        $this->dirty = [];

        return $summary;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['.', '_', '-'], ' ', $string)));
    }
}
