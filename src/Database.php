<?php declare(strict_types=1);

namespace Dbl;

use Dbl\Cache;
use Dbl\Collection;
use Dbl\Exception;
use Dbl\Summary;
use Dbl\Traits\MagicGetTrait;
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
     * @var array
     */
    protected $cache = [];

    /**
     * @var array
     */
    protected $settings = [
        'connections' => null,
        'cache' => null,
        'cache_ttl' => 2630000,
        'fetch_mode' => PDO::FETCH_ASSOC,
        'related_data_separator' => '__',
    ];

    /**
     * @param array $settings
     *
     * @return void
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings + $this->settings;
        static::$instance = $this;
    }

    /**
     * @return Database
     *
     * @throws Exception
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
     * @param string $connection
     *
     * @throws Exception
     *
     * @return Generator
     */
    public function fetch(string $query, array $params = [], string $connection = 'default'): Generator
    {
        $fetchMode = $this->settings['fetch_mode'];
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new Exception(
                'Error while preparing statement.',
                $query,
                $params
            );
        }

        $statement->execute($params);

        while ($result = $statement->fetch($fetchMode)) {
            yield new Collection($result);
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $connection
     *
     * @throws Exception
     *
     * @return Collection
     */
    public function fetchAll(string $query, array $params = [], string $connection = 'default'): Collection
    {
        $fetchMode = $this->settings['fetch_mode'];
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new Exception(
                'Error while preparing statement.',
                $query,
                $params
            );
        }

        $statement->execute($params);

        $results = $statement->fetchAll($fetchMode);

        foreach ($results as $k => $v) {
            $results[$k] = new Collection($v);
        }

        return new Collection($results);
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $connection
     *
     * @return Collection
     */
    public function first(string $query, array $params = [], string $connection = 'default'): Collection
    {
        $records = $this->fetchAll($query, $params, $connection);

        return $records[0] ?? new Collection();
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $connection
     *
     * @throws Exception
     *
     * @return string
     */
    public function single(string $query, array $params = [], string $connection = 'default'): string
    {
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new Exception(
                'Error while preparing statement.',
                $query,
                $params
            );
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
     *
     * @return Summary
     */
    public function execute(string $query, array $params = [], string $connection = 'default'): Summary
    {
        $pdo = $this->getPDO($connection);
        $statement = $pdo->prepare($query);

        if ($statement === false) {
            throw new Exception(
                'Error while preparing statement.',
                $query,
                $params
            );
        }

        $statement->execute($params);

        return new Summary($pdo, $statement);
    }

    /**
     * @param string $key
     * @param int $ttl Time to live in seconds
     * @param callable $callback
     *
     * @return mixed
     */
    public function cache(string $key, int $ttl, callable $callback)
    {
        if (is_null($this->settings['cache'])) {
            $value = $this->cache[$key] ?? $callback();
            $this->cache[$key] = $value;

            return $value;
        }

        $cache = $this->settings['cache'];

        if (!is_subclass_of($cache, Cache::class)) {
            throw new Exception('Cache class must implement the Cache interface.');
        }

        return $cache->remember($key, $ttl, $callback);
    }
}
