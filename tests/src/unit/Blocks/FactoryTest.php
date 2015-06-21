<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Tests\Blocks;

use Foil\Tests\TestCase;
use Foil\Blocks\Factory;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class FactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new Factory();
        $f1 = $factory->factory('strtolower');
        $f2 = $factory->factory('strtolower', 12);
        $f3 = $factory->factory('strtolower', __CLASS__);
        $mock = Mockery::mock('Foil\Contracts\BlockInterface');
        $f4 = $factory->factory('strtolower', get_class($mock));

        assertInstanceOf(Factory::CONTRACT, $f1);
        assertInstanceOf(Factory::CONTRACT, $f2);
        assertInstanceOf(Factory::CONTRACT, $f3);
        assertInstanceOf(Factory::CONTRACT, $f4);
        assertInstanceOf(Factory::DEFAULT_CLASS, $f1);
        assertInstanceOf(Factory::DEFAULT_CLASS, $f2);
        assertInstanceOf(Factory::DEFAULT_CLASS, $f3);
        assertInstanceOf(get_class($mock), $f4);
    }
}
