<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Summary;
use PHPUnit\Framework\TestCase;
use Pseudo\{Pdo, Result};

class SummaryTest extends TestCase
{
    public function testConstructor(): void
    {
        $pdo = new Pdo;
        $result = new Result;
        $query = 'INSERT INTO users VALUES(1, "John Doe")';

        $result->setInsertId(1);
        $result->setAffectedRowCount(1);
        $pdo->mock($query, $result);

        $statement = $pdo->prepare($query);

        $statement->execute();

        $summary = new Summary($pdo, $statement);

        $this->assertEquals(1, $summary->lastInsertId);
        $this->assertEquals(1, $summary->rowCount);
    }
}