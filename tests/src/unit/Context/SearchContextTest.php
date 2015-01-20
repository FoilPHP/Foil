<?php namespace Foil\Tests\Context;

use Foil\Tests\TestCase;
use Foil\Context\SearchContext;

class SearchContextTest extends TestCase
{
    public function testAccept()
    {
        $c = new SearchContext('foo\bar', ['foo' => 'bar']);
        assertTrue($c->accept('foo\bar.php'));
        assertTrue($c->accept('foo\bar\baz.php'));
        assertFalse($c->accept('foo'));
        assertFalse($c->accept('bar'));
        assertSame(['foo' => 'bar'], $c->provide());
    }
}
