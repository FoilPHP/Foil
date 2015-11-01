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
use Foil\Context\GlobalContext;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
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
