<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Context;

use Foil\Tests\TestCase;
use Foil\Context\RegexContext;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
class RegexContextTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorFailsIfBadArgument()
    {
        new RegexContext(['|^f[\w-]+\.php$|'], ['foo' => 'bar']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAcceptFailsIfBadTemplate()
    {
        $context = new RegexContext('/.+/');
        $context->accept(1);
    }

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
