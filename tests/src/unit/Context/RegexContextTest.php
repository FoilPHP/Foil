<?php namespace Foil\Tests\Context;

use Foil\Tests\TestCase;
use Foil\Context\RegexContext;

class RegexContextTest extends TestCase
{
    public function testAccept()
    {
        $c = new RegexContext('|^f[\w-]+\.php$|', ['foo' => 'bar']);
        assertFalse($c->accept('/path/to/foo.php'));
        assertFalse($c->accept('path/to/fo-o.php'));
        assertFalse($c->accept('path/to/foo.phtml'));
        assertFalse($c->accept('path/to/f.oo.php'));
        $c->acceptBasename();
        assertTrue($c->accept('/path/to/foo.php'));
        assertTrue($c->accept('path/to/fo-o.php'));
        assertFalse($c->accept('path/to/foo.phtml'));
        assertFalse($c->accept('path/to/f.oo.php'));
        assertSame(['foo' => 'bar'], $c->provide());
    }
}
