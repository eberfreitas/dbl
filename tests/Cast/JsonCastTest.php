<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Cast\JsonCast;
use Dbl\Column;
use PHPUnit\Framework\TestCase;

class JsonCastTest extends TestCase
{
    public function testCode()
    {
        $fakeColumn = new Column('test', 'json', true, null, []);

        $data = [
            'foo' => 'bar',
            'hello' => 'world',
        ];

        $json = '{"foo":"bar","hello":"world"}';

        $array = JsonCast::code($data, $fakeColumn);
        $this->assertIsArray($array);
        $this->assertEquals($data, $array);

        $array = JsonCast::code($json, $fakeColumn);
        $this->assertEquals($data, $array);
    }

    public function testDatabase()
    {
        $fakeColumn = new Column('test', 'varchar', true, null, []);

        $data = [
            'foo' => 'bar',
            'hello' => 'world',
        ];

        $json = '{"foo":"bar","hello":"world"}';

        $string = JsonCast::database($json, $fakeColumn);
        $this->assertIsString($string);
        $this->assertEquals($json, $string);

        $string = JsonCast::database($data, $fakeColumn);
        $this->assertEquals($json, $string);
    }
}
