<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    public function testConstructor(): void
    {
        $column = new Column(
            'test',
            'string',
            false,
            255,
            ['test' => 123]
        );

        $this->assertInstanceOf(Column::class, $column);
        $this->assertEquals('test', $column->name);
        $this->assertEquals('string', $column->type);
        $this->assertEquals(false, $column->null);
        $this->assertEquals(255, $column->length);
    }
}