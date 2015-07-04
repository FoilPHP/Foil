<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Blocks;

use Foil\Tests\TestCase;
use Foil\Blocks\Blocks;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class BlocksTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadName()
    {
        /** @var \Foil\Blocks\Factory $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');

        $blocks = new Blocks($factory);
        $blocks->add(true, function ($val) {
            return $val;
        });
    }

    public function testAdd()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');
        /** @var \Foil\Blocks\Block|\Mockery\MockInterface $block */
        $block = Mockery::mock('Foil\Blocks\Block');

        $func = function () {
            return 'foo';
        };

        $factory->shouldReceive('factory')
                ->once()
                ->with($func, null)
                ->andReturn($block);

        $blocks = new Blocks($factory);
        $blocks->add('foo', $func);

        /** @var array $added */
        $added = $this->accessPrivateProperty('blocks', $blocks);

        assertArrayHasKey('foo', $added);
        assertSame($block, $added['foo']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOpenFailsIfBadName()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');
        $blocks = new Blocks($factory);
        $blocks->open(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOpenFailIfNotRegisteredBlock()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');
        $blocks = new Blocks($factory);
        $blocks->open('foo');
    }

    public function testOpen()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');

        /** @var \Foil\Blocks\Block|\Mockery\MockInterface $block */
        $block = Mockery::mock('Foil\Blocks\Block');
        $block->shouldReceive('open')
              ->with(['foo', 'bar'])
              ->once()
              ->andReturnNull();

        $blocks = new Blocks($factory);

        $this->bindClosure(function ($block) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->blocks['foo'] = $block;
        }, $blocks, [$block]);

        $blocks->open('foo', ['foo', 'bar']);

        /** @var \SplStack $buffers */
        $buffers = $this->accessPrivateProperty('buffers', $blocks);

        assertInstanceOf('\SplStack', $buffers);
        $buffer = $buffers->pop();

        assertInternalType('array', $buffer);
        assertSame($block, $buffer[0]);
        assertSame('foo', $buffer[1]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCloseFailsIfNoBuffers()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');
        $blocks = new Blocks($factory);
        $blocks->close();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCloseFailsIfBadBuffer()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');
        $blocks = new Blocks($factory);

        /** @var \Foil\Blocks\Block|\Mockery\MockInterface $block */
        $block = Mockery::mock('Foil\Blocks\Block');
        $block->shouldReceive('open')->andReturnNull();

        $this->bindClosure(function ($block) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->blocks = ['foo' => $block, 'bar' => $block];
        }, $blocks, [$block]);

        $blocks->open('foo');
        $blocks->close('bar');
    }

    public function testClose()
    {
        /** @var \Foil\Blocks\Factory|\Mockery\MockInterface $factory */
        $factory = Mockery::mock('Foil\Blocks\Factory');
        /** @var \Foil\Blocks\Block|\Mockery\MockInterface $block */
        $block = Mockery::mock('Foil\Blocks\Block');
        $block->shouldReceive('close')->once()->andReturn('Closed!');

        $blocks = new Blocks($factory);

        $this->bindClosure(function ($block) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->buffers->push([$block, 'foo']);
        }, $blocks, [$block]);

        assertSame('Closed!', $blocks->close('foo'));
    }
}
