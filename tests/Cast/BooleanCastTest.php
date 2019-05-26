<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Cast\BooleanCast;
use Dbl\Column;
use PHPUnit\Framework\TestCase;

class BooleanCastTest extends TestCase
{
    public function testCode()
    {
        $fakeColumn = new Column('test', 'boolean', true, null, []);

        $shouldBeTrue = BooleanCast::code('true', $fakeColumn);
        $this->assertIsBool($shouldBeTrue);
        $this->assertEquals(true, $shouldBeTrue);

        $shouldBeTrue = BooleanCast::code(1, $fakeColumn);
        $this->assertEquals(true, $shouldBeTrue);

        $shouldBeFalse = BooleanCast::code('', $fakeColumn);
        $this->assertEquals(false, $shouldBeFalse);

        $shouldBeFalse = BooleanCast::code('0', $fakeColumn);
        $this->assertEquals(false, $shouldBeFalse);
    }

    public function testDatabase()
    {
        $fakeColumn = new Column('test', 'varchar', true, null, []);

        $shouldBeTrue = BooleanCast::database('true', $fakeColumn);
        $this->assertIsInt($shouldBeTrue);
        $this->assertEquals(1, $shouldBeTrue);

        $shouldBeTrue = BooleanCast::database(1, $fakeColumn);
        $this->assertEquals(1, $shouldBeTrue);

        $shouldBeFalse = BooleanCast::database('', $fakeColumn);
        $this->assertEquals(0, $shouldBeFalse);

        $shouldBeFalse = BooleanCast::database('0', $fakeColumn);
        $this->assertEquals(0, $shouldBeFalse);
    }
}
