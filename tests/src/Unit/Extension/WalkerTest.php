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
use Foil\Extensions\Walker;
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

    /**
     * @param  bool $autoescape
     * @return \Foil\Extensions\Walker|\Mockery\MockInterface
     */
    private function getWalker($autoescape = true)
    {
        /** @var \Foil\Kernel\Command $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        /** @var \Foil\Kernel\Escaper|\Mockery\MockInterface $escaper */
        $escaper = Mockery::mock('\Foil\Kernel\Escaper');
        $escaper->shouldReceive('escape')->andReturnUsing(function ($data) {
            if (is_string($data)) {
                return htmlentities($data);
            } elseif (is_array($data)) {
                return array_map('htmlentities', $data);
            }

            return '';
        });
        $options = ['autoescape' => $autoescape];

        return new Walker($command, $escaper, $options);
    }

    public function testWalkArray()
    {
        $w = $this->getWalker();
        $expected = '<li>A - foo - bar</li><li>B - foo - bar</li><li>C - foo - bar</li>';
        assertSame($expected, $w->walk(['A', 'B', 'C'], '<li>%s %s %s</li>', '- foo', '- bar'));
    }

    public function testWalkArrayEscape()
    {
        $w = $this->getWalker();
        $expected = '<li>&quot;A&quot; &lt;foo&gt;</li><li>&quot;B&quot; &lt;foo&gt;</li>';
        assertSame($expected, $w->walk(['"A"', '"B"'], '<li>%s %s</li>', '<foo>'));
    }

    public function testWalkArrayArray()
    {
        $w = $this->getWalker();
        $expected = "<p>Tom is 30 years old and comes from London</p>"
            ."<p>Dick is 33 years old and comes from New York</p>"
            ."<p>Harry is 25 years old and comes from Berlin</p>";
        $data = [['Tom', 'London', 30], ['Dick', 'New York', 33], ['Harry', 'Berlin', 25]];
        assertSame($expected, $w->walk($data, '<p>%1$s is %3$d years old and comes from %2$s</p>'));
    }

    public function testWalkIterator()
    {
        $w = $this->getWalker();
        $expected = '<li>&quot;A&quot; foo</li><li>&quot;B&quot; foo</li><li>&quot;C&quot; foo</li>';
        $it = new ArrayIterator(['"A"', '"B"', '"C"']);
        assertSame($expected, $w->walk($it, '<li>%s %s</li>', 'foo'));
    }

    public function testWalkIfBool()
    {
        $w = $this->getWalker();
        $expected = '<li>A - foo</li><li>B - foo</li><li>C - foo</li>';
        assertSame($expected, $w->walkIf(['A', 'B', 'C'], true, '<li>%s %s</li>', '- foo'));
        assertSame('', $w->walkIf(['A', 'B', 'C'], false, '<li>%s %s</li>', '- foo'));
    }

    public function testWalkIfCallback()
    {
        $w = $this->getWalker();
        $expected = '<li>A - foo</li><li>B - foo</li><li>C - foo</li>';
        assertSame(
            $expected,
            $w->walkIf(['A', 'B', 'C'], function () {
                return 1 === 1;
            }, '<li>%s %s</li>', '- foo')
        );
        assertSame(
            '',
            $w->walkIf(['A', 'B', 'C'], function () {
                return 1 === 2;
            }, '<li>%s %s</li>', '- foo')
        );
    }

    public function testWalkWrap()
    {
        $w = $this->getWalker();
        $expected = '<ul><li>A - foo</li><li>B - foo</li><li>C - foo</li></ul>';
        assertSame(
            $expected,
            $w->walkWrap(['A', 'B', 'C'], '<ul>%s</ul>', '<li>%s %s</li>', '- foo')
        );
    }

    public function testWalkWrapDefault()
    {
        $w = $this->getWalker();
        $expected = '<li>A</li><li>B</li><li>C</li>';
        assertSame(
            $expected,
            $w->walkWrap(['A', 'B', 'C'], '', '<li>%s</li>')
        );
    }

    public function testWalkWrapIf()
    {
        $w = $this->getWalker();
        $expected = '<ul><li>A - foo</li><li>B - foo</li><li>C - foo</li></ul>';
        assertSame(
            $expected,
            $w->walkWrapIf(['A', 'B', 'C'], true, '<ul>%s</ul>', '<li>%s %s</li>', '- foo')
        );
        assertSame(
            '',
            $w->walkWrapIf(['A', 'B', 'C'], false, '<ul>%s</ul>', '<li>%s %s</li>', '- foo')
        );
    }
}
