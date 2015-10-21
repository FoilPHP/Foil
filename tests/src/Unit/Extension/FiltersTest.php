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
use Foil\Extensions\Filters;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class FiltersTest extends TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFirstFailsIfBadArgs()
    {
        $filters = new Filters();
        $filters->first(true);
    }

    public function testFirst()
    {
        $filters = new Filters();
        assertSame('foo', $filters->first(['foo', 'bar']));
        assertSame('foo', $filters->first(['a' => 'foo', 'foo' => 'bar']));
        assertSame('F', $filters->first('Foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLastFailsIfBadArgs()
    {
        $filters = new Filters();
        $filters->last(true);
    }

    public function testLast()
    {
        $filters = new Filters();
        assertSame('bar', $filters->last(['foo', 'bar']));
        assertSame('bar', $filters->last(['a' => 'foo', 'foo' => 'bar']));
        assertSame('o', $filters->last('Foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testChunkFailsInNoNumber()
    {
        $filters = new Filters();
        $filters->chunk([], '3');
    }

    public function testChunk()
    {
        $filters = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $three = [['a', 'b', 'c'], ['d', 'e', 'f'], ['g']];
        $threeFilled = [['a', 'b', 'c'], ['d', 'e', 'f'], ['g', 'fill', 'fill']];
        $dataFilled = [['a', 'b', 'c', 'd', 'e', 'f', 'g', 'fill', 'fill', 'fill']];
        $dataAssoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        $threeAssoc = [['A', 'B', 'C'], ['D', 'E', 'F']];
        $datAssocFilled = [['A', 'B', 'C', 'D', 'E', 'F', 'fill', 'fill', 'fill', 'fill']];
        assertSame($three, $filters->chunk($data, 3));
        assertSame([$data], $filters->chunk($data, 7));
        assertSame([$data], $filters->chunk($data, 10));
        assertSame($threeFilled, $filters->chunk($data, 3, 'fill'));
        assertSame([$data], $filters->chunk($data, 7, 'fill'));
        assertSame($dataFilled, $filters->chunk($data, 10, 'fill'));
        assertSame($threeAssoc, $filters->chunk($dataAssoc, 3));
        assertSame([array_values($dataAssoc)], $filters->chunk($dataAssoc, 10));
        assertSame($threeAssoc, $filters->chunk($dataAssoc, 3, 'fill'));
        assertSame($datAssocFilled, $filters->chunk($dataAssoc, 10, 'fill'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIsFirstFailsIfBadArgs()
    {
        $filters = new Filters();
        $filters->isFirst(true, '');
    }

    public function testIsFirst()
    {
        $filters = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        $str = 'ABCDEFGHIJKL';
        assertTrue($filters->isFirst($data, 'a'));
        assertTrue($filters->isFirst($data_assoc, 'A'));
        assertTrue($filters->isFirst($str, 'A'));
        assertFalse($filters->isFirst($data, 'c'));
        assertFalse($filters->isFirst($data_assoc, 'a'));
        assertFalse($filters->isFirst($str, 'a'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIsLastFailsIfBadArgs()
    {
        $filters = new Filters();
        $filters->isLast(true, '');
    }

    public function testIsLast()
    {
        $filters = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        $str = 'ABCDEFGHIJKL';
        assertTrue($filters->isLast($data, 'g'));
        assertTrue($filters->isLast($data_assoc, 'F'));
        assertTrue($filters->isLast($str, 'L'));
        assertFalse($filters->isLast($data, 'f'));
        assertFalse($filters->isLast($data_assoc, 'f'));
        assertFalse($filters->isLast($str, 'l'));
    }

    public function testIndex()
    {
        $filters = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        assertSame(1, $filters->index($data, 'a'));
        assertSame(-1, $filters->index($data, 'A'));
        assertSame(1, $filters->index($data_assoc, 'A'));
        assertSame(-1, $filters->index($data_assoc, 'H'));
        assertTrue($filters->index($data, 'a', 1));
        assertFalse($filters->index($data, 'a', 0));
        assertFalse($filters->index($data_assoc, 'A', 0));
        assertFalse($filters->index($data_assoc, 'A', 'a'));
    }

    public function testIndex0()
    {
        $filters = new Filters();
        $data = ['a', 'b', 'c', 'd', 'e', 'f'];
        $data_assoc = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'];
        assertSame(0, $filters->index0($data, 'a'));
        assertSame(-1, $filters->index0($data, 'A'));
        assertSame(0, $filters->index0($data_assoc, 'A'));
        assertSame(-1, $filters->index0($data_assoc, 'H'));
        assertTrue($filters->index0($data, 'a', 0));
        assertTrue($filters->index0($data_assoc, 'A', 0));
        assertFalse($filters->index0($data, 'a', 1));
        assertFalse($filters->index0($data_assoc, 'A', 1));
        assertFalse($filters->index0($data_assoc, 'A', 'a'));
    }
}
