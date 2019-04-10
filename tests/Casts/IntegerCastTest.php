<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Casts\IntegerCast;
use Dbl\Column;
use PHPUnit\Framework\TestCase;

class IntegerCastTest extends TestCase
{
    public function testCode()
    {
        $fakeColumn = new Column('test', 'int', true, null, []);

        $int = IntegerCast::code('1.23', $fakeColumn);
        $this->assertIsInt($int);
        $this->assertEquals(1, $int);
    }

    public function testDatabase()
    {
        $fakeColumn = new Column('test', 'int', true, null, []);

        $int = IntegerCast::database('1.23', $fakeColumn);
        $this->assertIsInt($int);
        $this->assertEquals(1, $int);
    }
}
