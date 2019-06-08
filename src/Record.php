<?php

declare(strict_types=1);

namespace Dbl;

use Dbl\Exception\Exception;
use Dbl\Exception\PDOPrepareException;
use Dbl\Helper\StringHelper as S;

abstract class Record extends Collection
{
    /**
     * @var string
     */
    protected $tableClass = '';

    /**
     * @var Table
     */
    private $table;

    /**
     * @var array
     */
    private $raw;

    /**
     * @var array
     */
    private $dirty = [];

    /**
     * @var Database
     */
    private $db;

    /**
     * @param array $data
     *
     * @throws Exception
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
        $class = '\\' . trim(__NAMESPACE__, '\\') . '\\' . S::camelCase($table);

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
        $value = $this->data[$offset] ?? null;
        $original = $this->raw[$offset] ?? null;

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
        $method = 'set' . S::camelCase($key);

        if (method_exists($this, $method)) {
            $value = call_user_func([$this, $method], $value);
        }

        return $value;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function merge(array $data): void
    {
        foreach ($data as $k => $v) {
            $original = $this->data[$k] ?? null;

            if ($v !== $original) {
                $this->__set($k, $v);
            }
        }
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

        $method = 'get' . S::camelCase($offset);

        if (method_exists($this, $method)) {
            $value = call_user_func([$this, $method], $value);
        }

        return $value;
    }

    /**
     * @throws Exception
     * @throws PDOPrepareException
     *
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
}
