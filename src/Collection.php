<?php declare(strict_types=1);

namespace Dbl;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

class Collection implements
    ArrayAccess,
    Countable,
    IteratorAggregate,
    JsonSerializable,
    Serializable
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->data = $data;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->data);
    }

    /**
     * @param string $data
     *
     * @return void
     */
    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function __get($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function __set($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param callable $callback
     *
     * @return Collection
     */
    public function filter(callable $callback): Collection
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            if ($callback($key, $value)) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * @param callable $callback
     *
     * @return Collection
     */
    public function map(callable $callback): Collection
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            $return = $callback($key, $value);

            if (is_array($return)) {
                list($k, $v) = $return;
                $result[$k] = $v;
            } else {
                $result[$key] = $return;
            }
        }

        return new static($result);
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function pluck(callable $callback)
    {
        foreach ($this->data as $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->data) === 0;
    }

    /**
     * @return array
     */
    public function raw(): array
    {
        return $this->data;
    }
}
