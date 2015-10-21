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
use Foil\Context\SearchContext;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class SearchContextTest extends TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorFailsIfBadNeedle()
    {
        new SearchContext(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAcceptFailsIfBadTemplate()
    {
        $c = new SearchContext('foo\bar');
        $c->accept(true);
    }

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
