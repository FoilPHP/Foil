<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Extension;

use Foil\Tests\TestCase;
use Mockery;
use Foil;
use ArrayIterator;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class WalkerTest extends TestCase
{
    private function getWalkerMocked()
    {
        $walker = Mockery::mock('Foil\Extensions\Walker')->makePartial();
        $walker->shouldReceive('option')->with('autoescape')->andReturn(true);
        $walker->shouldReceive('api->arraize')->andReturnUsing(function ($value) {
            return Foil\arraize($value, true);
        });
        $walker->shouldReceive('api->entities')->andReturnUsing(function ($var) {
            return Foil\entities($var);
        });

        return $walker;
    }

    public function testWalkArray()
    {
        $w = $this->getWalkerMocked();
        $expected = '<li>A - foo - bar</li><li>B - foo - bar</li><li>C - foo - bar</li>';
        assertSame($expected, $w->walk(['A', 'B', 'C'], '<li>%s %s %s</li>', '- foo', '- bar'));
    }

    public function testWalkArrayEscape()
    {
        $w = $this->getWalkerMocked();
        $expected = '<li>&quot;A&quot; &lt;foo&gt;</li><li>&quot;B&quot; &lt;foo&gt;</li>';
        assertSame($expected, $w->walk(['"A"', '"B"'], '<li>%s %s</li>', '<foo>'));
    }

    public function testWalkArrayArray()
    {
        $w = $this->getWalkerMocked();
        $expected = "<p>Tom is 30 years old and comes from London</p>"
            ."<p>Dick is 33 years old and comes from New York</p>"
            ."<p>Harry is 25 years old and comes from Berlin</p>";
        $data = [['Tom', 'London', 30], ['Dick', 'New York', 33], ['Harry', 'Berlin', 25]];
        assertSame($expected, $w->walk($data, '<p>%1$s is %3$d years old and comes from %2$s</p>'));
    }

    public function testWalkIterator()
    {
        $w = $this->getWalkerMocked();
        $expected = '<li>&quot;A&quot; foo</li><li>&quot;B&quot; foo</li><li>&quot;C&quot; foo</li>';
        $it = new ArrayIterator(['"A"', '"B"', '"C"']);
        assertSame($expected, $w->walk($it, '<li>%s %s</li>', 'foo'));
    }

    public function testWalkIfBool()
    {
        $w = $this->getWalkerMocked();
        $expected = '<li>A - foo</li><li>B - foo</li><li>C - foo</li>';
        assertSame($expected, $w->walkIf(['A', 'B', 'C'], true, '<li>%s %s</li>', '- foo'));
        assertSame('', $w->walkIf(['A', 'B', 'C'], false, '<li>%s %s</li>', '- foo'));
    }

    public function testWalkIfCallback()
    {
        $w = $this->getWalkerMocked();
        $expected = '<li>A - foo</li><li>B - foo</li><li>C - foo</li>';
        assertSame($expected, $w->walkIf(['A', 'B', 'C'], function () {
            return 1 === 1;
        }, '<li>%s %s</li>', '- foo'));
        assertSame('', $w->walkIf(['A', 'B', 'C'], function () {
            return 1 === 2;
        }, '<li>%s %s</li>', '- foo'));
    }

    public function testWalkWrap()
    {
        $w = $this->getWalkerMocked();
        $expected = '<ul><li>A - foo</li><li>B - foo</li><li>C - foo</li></ul>';
        assertSame($expected,
            $w->walkWrap(['A', 'B', 'C'], '<ul>%s</ul>', '<li>%s %s</li>', '- foo'));
    }

    public function testWalkWrapIf()
    {
        $w = $this->getWalkerMocked();
        $expected = '<ul><li>A - foo</li><li>B - foo</li><li>C - foo</li></ul>';
        assertSame($expected,
            $w->walkWrapIf(['A', 'B', 'C'], true, '<ul>%s</ul>', '<li>%s %s</li>', '- foo'));
        assertSame('',
            $w->walkWrapIf(['A', 'B', 'C'], false, '<ul>%s</ul>', '<li>%s %s</li>', '- foo'));
    }
}
