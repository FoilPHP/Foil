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

use Foil\Blocks\Block;
use Foil\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class BlockTest extends TestCase
{

    public function testOpen()
    {
        $block = new Block('strtoupper');
        $block->open();
        echo 'Foo!';
        $data = ob_get_clean();
        $this->expectOutputString('');
        assertSame('Foo!', $data);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCloseFailsIfNotOpened()
    {
        $block = new Block('strtolower');
        $block->open();
        $block->close();
        $block->close();
    }

    public function testOpenClose()
    {
        $block = new Block('str_repeat');
        $block->open([2]);
        echo 'Hello';
        $data = $block->close();
        $this->expectOutputString('');
        assertSame('HelloHello', $data);
    }
}
