<?php declare(strict_types=1);

namespace Dbl;

use Dbl\Traits\ObjectMagicGetTrait;

class Summary
{
    use ObjectMagicGetTrait;

    /**
     * @var int
     */
    protected $rowCount;

    /**
     * @var string
     */
    protected $errorCode;

    /**
     * @var array
     */
    protected $errorInfo;

    /**
     * @var string
     */
    protected $lastInsertId;

    /**
     * @param \PDO $pdo
     * @param \PDOStatement $statement
     */
    public function __construct(\PDO $pdo, \PDOStatement $statement)
    {
        $this->rowCount = $statement->rowCount();
        $this->errorCode = $statement->errorCode();
        $this->errorInfo = $statement->errorInfo();
        $this->lastInsertId = $pdo->lastInsertId();
    }
}
