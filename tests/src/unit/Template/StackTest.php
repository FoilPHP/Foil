<?php namespace Foil\Tests\Template;

use Foil\Tests\TestCase;
use Foil\Template\Stack;
use Mockery;

class StackTest extends TestCase
{
    public function testFactory()
    {
        $api = Mockery::mock('Foil\API');
        $api->shouldReceive('foil->factory')->andReturnValues(['foo', 'bar', 'baz']);
        $s = new Stack($api);
        $s->factory('/path/to/foo');
        $s->factory('/path/to/bar');
        $s->factory('/path/to/baz');
        assertSame('baz', $s->template());
        assertSame(3, $s->count());
        $s->pop();
        assertSame('bar', $s->template());
        assertSame(2, $s->count());
        $s->pop();
        assertSame('foo', $s->template());
        assertSame(1, $s->count());
    }
}
