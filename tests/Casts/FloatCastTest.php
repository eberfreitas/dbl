<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Casts\FloatCast;
use Dbl\Column;
use PHPUnit\Framework\TestCase;

class FloatCastTest extends TestCase
{
    public function testCode()
    {
        $fakeColumn = new Column('test', 'varchar', true, null, []);

        $float = FloatCast::code(1.234, $fakeColumn);
        $this->assertIsFloat($float);
        $this->assertEquals(1.234, $float);

        $fakeColumn = new Column('test', 'varchar', true, null, ['numeric_precision' => 2]);

        $float = FloatCast::code(1.234, $fakeColumn);
        $this->assertEquals(1.23, $float);
    }

    public function testDatabase()
    {
        $fakeColumn = new Column('test', 'varchar', true, null, []);

        $float = FloatCast::database(1.234, $fakeColumn);
        $this->assertIsFloat($float);
        $this->assertEquals(1.234, $float);
    }
}
