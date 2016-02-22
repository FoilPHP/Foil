<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Template;

use Foil\Tests\TestCase;
use Foil\Template\Stack;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class StackTest extends TestCase
{
    public function testFactory()
    {
        /** @var \Foil\Template\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Template\Factory');
        $factory->shouldReceive('factory')->andReturnValues(['foo', 'bar', 'baz']);
        $stack = new Stack($factory);
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        $stack->factory('/path/to/foo', $engine);
        $stack->factory('/path/to/bar', $engine);
        $stack->factory('/path/to/baz', $engine);
        assertSame('baz', $stack->template());
        assertSame(3, $stack->count());
        $stack->pop();
        assertSame('bar', $stack->template());
        assertSame(2, $stack->count());
        $stack->pop();
        assertSame('foo', $stack->template());
        assertSame(1, $stack->count());
    }
}
