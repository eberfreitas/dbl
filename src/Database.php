<?php declare(strict_types=1);

namespace Dbl;

use Dbl\Collection;
use Dbl\Exception;
use Dbl\Summary;
use Dbl\Traits\ObjectMagicGetTrait;
use Generator;

class Database
{
    use ObjectMagicGetTrait;

    /**
     * @var Database
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     *
     * @return void
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        static::$instance = $this;
    }

    /**
     * @var string|null $key
     * @var mixed $default
     *
     * @return mixed
     */
    public function getSettings(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->settings;
        }

        return $this->settings[$key] ?? $default;
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
     * @return \PDO
     */
    public function getPDO(string $connection = 'default'): \PDO
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
        $fetchMode = $this->getSettings('fetch_mode', \PDO::FETCH_ASSOC);
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
        $fetchMode = $this->getSettings('fetch_mode', \PDO::FETCH_ASSOC);
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
}
