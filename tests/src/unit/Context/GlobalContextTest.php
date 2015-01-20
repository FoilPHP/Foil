<?php namespace Foil\Tests\Context;

use Foil\Tests\TestCase;
use Foil\Context\GlobalContext;

class GlobalContextTest extends TestCase
{
    public function testAccept()
    {
        $c = new GlobalContext(['foo' => 'bar']);
        assertTrue($c->accept('foo'));
        assertTrue($c->accept('fooo'));
        assertFalse($c->accept(false));
        assertSame(['foo' => 'bar'], $c->provide());
    }
}
