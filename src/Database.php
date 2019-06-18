<?php

declare(strict_types=1);

namespace Dbl;

use Dbl\Exception\Exception;
use Dbl\Exception\PDOPrepareException;
use Dbl\Helper\MagicGetTrait;
use Generator;
use PDO;

class Database
{
    use MagicGetTrait;

    /**
     * @var Database|null
     */
    protected static $instance;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var array
     */
    protected $settings = [
        'connections' => null,
        'cache' => null,
        'cache_settings' => [],
        'fetch_mode' => PDO::FETCH_ASSOC,
        'related_data_separator' => '__',
    ];

    /**
     * @param array $settings
     *
     * @throws Exception
     *
     * @return void
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings + $this->settings;

        /** @var class-string|null $cacheClass */
        $cacheClass = $this->settings['cache'] ?? null;

        if (is_null($cacheClass)) {
            $this->cache = new Cache($this->settings['cache_settings']);
        } else {
            if (!is_subclass_of($cacheClass, Cache::class)) {
                throw new Exception('Given cache class must extend the Dbl\Cache class');
            }

            $this->cache = new $cacheClass($this->settings['cache_settings']);
        }

        static::$instance = $this;
    }

    /**
     * @throws Exception
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (is_null(self::$instance)) {
            throw new Exception('The `Dbl::Database` class must be initialized first.');
        }

        return self::$instance;
    }

    /**
     * @param string $connection
     *
     * @throws Exception
     *
     * @return PDO
     */
    public function getPDO(string $connection = 'default'): PDO
    {
        $pdo = $this->settings['connections'][$connection] ?? null;

        if (is_null($pdo)) {
            throw new Exception(sprintf('No "%s" PDO object found in settings.', $connection));
        }

        return $pdo;
    }

    /**
     * @param string $query
     * @param array $params
     * @param class-string $collection
     * @param string $connection
     *
     * @throws Exception
     * @throws PDOPrepareException
     *
     * @return Generator
     */
    public function fetch(
        string $query,
        array $params = [],
        string $collection = Collection::class,
        string $connection = 'default'
    ): Generator
    {
        $fetchMode = $this->settings['fetch_mode'];
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new PDOPrepareException($query, $params);
        }

        $statement->execute($params);

        while ($result = $statement->fetch($fetchMode)) {
            yield new $collection($result);
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @param class-string $collection
     * @param string $connection
     *
     * @throws Exception
     * @throws PDOPrepareException
     *
     * @return Collection
     */
    public function fetchAll(
        string $query,
        array $params = [],
        string $collection = Collection::class,
        string $connection = 'default'
    ): Collection
    {
        $fetchMode = $this->settings['fetch_mode'];
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new PDOPrepareException($query, $params);
        }

        $statement->execute($params);

        $results = $statement->fetchAll($fetchMode);

        foreach ($results as $k => $v) {
            $results[$k] = new $collection($v);
        }

        return new Collection($results);
    }

    /**
     * @param string $query
     * @param array $params
     * @param class-string $collection
     * @param string $connection
     *
     * @throws Exception
     * @throws PDOPrepareException
     *
     * @return Collection
     */
    public function first(
        string $query,
        array $params = [],
        string $collection = Collection::class,
        string $connection = 'default'
    ): Collection
    {
        $records = $this->fetchAll($query, $params, $collection, $connection);

        return $records[0] ?? new $collection([]);
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $connection
     *
     * @throws Exception
     * @throws PDOPrepareException
     *
     * @return string
     */
    public function single(string $query, array $params = [], string $connection = 'default'): string
    {
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new PDOPrepareException($query, $params);
        }

        $statement->execute($params);

        return (string) $statement->fetchColumn(0);
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $connection
     *
     * @throws Exception
     * @throws PDOPrepareException
     *
     * @return Summary
     */
    public function execute(string $query, array $params = [], string $connection = 'default'): Summary
    {
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new PDOPrepareException($query, $params);
        }

        $statement->execute($params);

        return new Summary($pdo, $statement);
    }

    /**
     * @param string $key
     * @param callable $callback
     *
     * @return mixed
     */
    public function cache(string $key, callable $callback)
    {
        return $this->cache->remember($key, $callback);
    }
}
