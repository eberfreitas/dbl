<?php declare(strict_types=1);

namespace Tests\Unit;

use Dbl\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    protected $sampleData = [
        'foo' => 'bar',
        'hello' => 'world',
    ];

    protected $sampleCollection;

    protected function setUp(): void
    {
        $this->sampleCollection = new Collection($this->sampleData);
    }

    public function testArrayAccess(): void
    {
        $collection = $this->sampleCollection;

        $this->assertArrayHasKey('foo', $collection);
        $this->assertEquals('bar', $collection['foo']);
        $this->assertEquals('world', $collection['hello']);

        $collection['baz'] = 'qux';
        $collection[] = 'hi';

        $this->assertEquals('qux', $collection['baz']);
        $this->assertEquals('hi', $collection[0]);

        unset($collection['baz']);

        $this->assertArrayNotHasKey('baz', $collection);
    }

    public function testIteratorAggregate(): void
    {
        $collection = $this->sampleCollection;
        $data = $this->sampleData;

        foreach ($collection as $k => $v) {
            $this->assertEquals($v, $data[$k]);
        }
    }

    public function testCountable(): void
    {
        $this->assertCount(2, $this->sampleCollection);
    }

    public function testSerializable(): void
    {
        $expected = serialize($this->sampleData);
        $serialized = serialize($this->sampleCollection);

        $this->assertStringContainsString($expected, $serialized);

        $unserialized = unserialize($serialized);

        $this->assertEquals('bar', $unserialized['foo']);
    }

    public function testJsonSerializable(): void
    {
        $expected = json_encode($this->sampleData);
        $encoded = json_encode($this->sampleCollection);

        $this->assertEquals($expected, $encoded);
    }

    public function testMagicSetGet(): void
    {
        $this->assertEquals('bar', $this->sampleCollection->foo);

        $this->sampleCollection->name = 'John Doe';

        $this->assertEquals('John Doe', $this->sampleCollection['name']);
    }

    public function testFilter(): void
    {
        $collection = $this->sampleCollection->filter(function($k, $v) {
            return $v === 'bar';
        });

        $this->assertArrayNotHasKey('hello', $collection);
        $this->assertCount(1, $collection);
    }

    public function testMap(): void
    {
        $collection = $this->sampleCollection->map(function($k, $v) {
            return [$k . '_transf', $v . '_transf'];
        });

        $this->assertArrayHasKey('foo_transf', $collection);
        $this->assertEquals('bar_transf', $collection['foo_transf']);
    }

    public function testRaw(): void
    {
        $this->assertEquals($this->sampleData, $this->sampleCollection->raw());
    }
}
