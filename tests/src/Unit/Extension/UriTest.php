<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Extension;

use Foil\Tests\TestCase;
use Foil\Extensions\Uri;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class UriTest extends TestCase
{

    public function testHome()
    {
        $u = new Uri();
        $u->setup();
        assertSame('Yes', $u->is('/', 'Yes', 'No'));
        assertSame('Yes', $u->is([1, '/'], 'Yes', 'No'));
    }

    public function testIsWithArray()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz']);
        assertSame('Yes', $u->is([2, 'bar'], 'Yes', 'No'));
        assertTrue($u->is([2, 'bar']));
        assertSame('Yes', $u->is([3, '/baz'], 'Yes', 'No'));
        assertTrue($u->is([3, '/baz']));
        assertSame('No', $u->is([1, '/baz'], 'Yes', 'No'));
        assertSame('', $u->is([2, '/baz']));
    }

    public function testIsWithHomeAndArray()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz', 'home' => '/foo/bar']);
        assertSame('Yes', $u->is([1, 'baz'], 'Yes', 'No'));
        assertTrue($u->is([1, 'baz']));
        assertSame('No', $u->is([3, 'baz'], 'Yes', 'No'));
        assertSame('', $u->is([3, '/baz']));
    }

    public function testIsWithString()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz']);
        assertSame('Yes', $u->is('/foo/bar/baz', 'Yes', 'No'));
        assertTrue($u->is('foo/bar/baz/'));
        assertSame('No', $u->is('/foo/bar/baz/foo', 'Yes', 'No'));
        assertSame('', $u->is('foo/bar'));
    }

    public function testIsWithHomeAndString()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz', 'home' => '/foo/bar']);
        assertSame('Yes', $u->is('baz', 'Yes', 'No'));
        assertTrue($u->is('/baz/'));
        assertSame('No', $u->is('/foo/bar/baz', 'Yes', 'No'));
        assertSame('', $u->is('foo/bar/baz'));
    }

    public function testHas()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz']);
        assertSame('Yes', $u->has('/foo', 'Yes', 'No'));
        assertTrue($u->has('/foo/bar/'));
        assertSame('No', $u->has('/foo/foo', 'Yes', 'No'));
        assertSame('', $u->has('foo/baz/bar'));
    }

    public function testHasWithHome()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz', 'home' => '/foo']);
        assertSame('Yes', $u->has('/bar', 'Yes', 'No'));
        assertTrue($u->has('bar/baz'));
        assertSame('No', $u->has('foo/bar', 'Yes', 'No'));
        assertSame('', $u->has('foo/bar/baz'));
    }

    public function testMatch()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz']);
        assertSame('Yes', $u->match('foo', 'Yes', 'No'));
        assertSame('Yes', $u->match('^foo', 'Yes', 'No'));
        assertTrue($u->match('.+bar'));
        assertSame('No', $u->match('^bar', 'Yes', 'No'));
        assertSame('', $u->match('[0-9]'));
    }

    public function testMatchWithHome()
    {
        $u = new Uri();
        $u->setup(['pathinfo' => '/foo/bar/baz', 'home' => '/foo']);
        assertSame('Yes', $u->match('bar', 'Yes', 'No'));
        assertSame('Yes', $u->match('^bar', 'Yes', 'No'));
        assertTrue($u->match('.*baz'));
        assertSame('No', $u->match('^foo', 'Yes', 'No'));
        assertSame('', $u->match('foo'));
    }
}
