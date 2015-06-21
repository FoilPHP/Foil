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

use Foil\Extensions\Blocks;
use Foil\Tests\TestCase;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class BlocksTest extends TestCase
{
    public function testProvideFunctions()
    {
        /** @var \Foil\Blocks\Blocks $blocks */
        $blocks = Mockery::mock('Foil\Blocks\Blocks');
        $extension = new Blocks($blocks);
        $functions = $extension->provideFunctions();
        assertInternalType('array', $functions);
        assertArrayHasKey('block', $functions);
        assertArrayHasKey('endblock', $functions);
        assertInternalType('callable', $functions['block']);
        assertInternalType('callable', $functions['endblock']);
    }

    public function testBlock()
    {
        /** @var \Foil\Blocks\Blocks|\Mockery\MockInterface $blocks */
        $blocks = Mockery::mock('Foil\Blocks\Blocks');
        $blocks->shouldReceive('open')
               ->once()
               ->with('foo', [1, 'bar'])
               ->andReturnNull();
        $extension = new Blocks($blocks);
        $functions = $extension->provideFunctions();
        $block = $functions['block'];
        $block('foo', 1, 'bar');
    }

    public function testEndBlock()
    {
        /** @var \Foil\Blocks\Blocks|\Mockery\MockInterface $blocks */
        $blocks = Mockery::mock('Foil\Blocks\Blocks');
        $blocks->shouldReceive('close')
               ->once()
               ->andReturn('Works!');
        $extension = new Blocks($blocks);
        $functions = $extension->provideFunctions();
        $block = $functions['endblock'];
        $this->expectOutputString('Works!');
        $block();
    }
}
