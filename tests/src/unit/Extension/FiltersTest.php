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
use Foil\Extensions\Filters;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class FiltersTest extends TestCase
{
    public function testFirst()
    {
        $f = new Filters();
        assertSame('foo', $f->first(['foo', 'bar']));
        assertSame('foo', $f->first(['a' => 'foo', 'foo' => 'bar']));
        assertSame('F', $f->first('Foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFirstFailsIfBadArgs()
    {
        $f = new Filters();
        $f->first(true);
    }

    public function testLast()
    {
        $f = new Filters();
        assertSame('bar', $f->last(['foo', 'bar']));
        assertSame('bar', $f->last(['a' => 'foo', 'foo' => 'bar']));
        assertSame('o', $f->last('Foo'));
    }

    public function testChunk()
    {
        $f = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $three = [['a', 'b', 'c'], ['d', 'e', 'f'], ['g']];
        $three_filled = [['a', 'b', 'c'], ['d', 'e', 'f'], ['g', 'fill', 'fill']];
        $data_filled = [['a', 'b', 'c', 'd', 'e', 'f', 'g', 'fill', 'fill', 'fill']];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        $three_assoc = [['A', 'B', 'C'], ['D', 'E', 'F']];
        $data_assoc_filled = [['A', 'B', 'C', 'D', 'E', 'F', 'fill', 'fill', 'fill', 'fill']];
        assertSame($three, $f->chunk($data, 3));
        assertSame([$data], $f->chunk($data, 7));
        assertSame([$data], $f->chunk($data, 10));
        assertSame($three_filled, $f->chunk($data, 3, 'fill'));
        assertSame([$data], $f->chunk($data, 7, 'fill'));
        assertSame($data_filled, $f->chunk($data, 10, 'fill'));
        assertSame($three_assoc, $f->chunk($data_assoc, 3));
        assertSame([array_values($data_assoc)], $f->chunk($data_assoc, 10));
        assertSame($three_assoc, $f->chunk($data_assoc, 3, 'fill'));
        assertSame($data_assoc_filled, $f->chunk($data_assoc, 10, 'fill'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIsFirstFailsIfBadArgs()
    {
        $f = new Filters();
        $f->isFirst(true, '');
    }

    public function testIsFirst()
    {
        $f = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        $str = 'ABCDEFGHIJKL';
        assertTrue($f->isFirst($data, 'a'));
        assertTrue($f->isFirst($data_assoc, 'A'));
        assertTrue($f->isFirst($str, 'A'));
        assertFalse($f->isFirst($data, 'c'));
        assertFalse($f->isFirst($data_assoc, 'a'));
        assertFalse($f->isFirst($str, 'a'));
    }

    public function testIsLast()
    {
        $f = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        $str = 'ABCDEFGHIJKL';
        assertTrue($f->isLast($data, 'g'));
        assertTrue($f->isLast($data_assoc, 'F'));
        assertTrue($f->isLast($str, 'L'));
        assertFalse($f->isLast($data, 'f'));
        assertFalse($f->isLast($data_assoc, 'f'));
        assertFalse($f->isLast($str, 'l'));
    }

    public function testIndex()
    {
        $f = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        assertSame(1, $f->index($data, 'a'));
        assertSame(-1, $f->index($data, 'A'));
        assertSame(1, $f->index($data_assoc, 'A'));
        assertSame(-1, $f->index($data_assoc, 'H'));
        assertTrue($f->index($data, 'a', 1));
        assertFalse($f->index($data, 'a', 0));
        assertFalse($f->index($data_assoc, 'A', 0));
        assertFalse($f->index($data_assoc, 'A', 'a'));
    }

    public function testIndex0()
    {
        $f = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        assertSame(0, $f->index0($data, 'a'));
        assertSame(-1, $f->index0($data, 'A'));
        assertSame(0, $f->index0($data_assoc, 'A'));
        assertSame(-1, $f->index0($data_assoc, 'H'));
        assertTrue($f->index0($data, 'a', 0));
        assertTrue($f->index0($data_assoc, 'A', 0));
        assertFalse($f->index0($data, 'a', 1));
        assertFalse($f->index0($data_assoc, 'A', 1));
        assertFalse($f->index0($data_assoc, 'A', 'a'));
    }
}
