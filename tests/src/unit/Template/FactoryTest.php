<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Template;

use Foil\Tests\TestCase;
use Foil\Template\Factory;
use Foil\Tests\API;
use ArrayObject;
use Pimple\Container;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class FactoryTest extends TestCase
{

    public function testFactoryStandardClass()
    {
        $container = new Container(['options' => ['template_class' => false]]);
        $e = new Factory(new ArrayObject(), new ArrayObject(), new API($container));
        $instance = $e->factory('one');
        assertInstanceOf('Foil\Template\Template', $instance);
    }

    public function testFactoryCustomDefaultClass()
    {
        $mock = Mockery::mock('Foil\\Contracts\\TemplateInterface');
        $class = get_class($mock);
        $container = new Container(['options' => ['template_class' => $class]]);
        $e = new Factory(new ArrayObject(), new ArrayObject(), new API($container));
        $instance = $e->factory('one');
        assertInstanceOf($class, $instance);
    }

    public function testFactoryCustomClass()
    {
        $mock = Mockery::mock('Foil\\Contracts\\TemplateInterface');
        $class = get_class($mock);
        $container = new Container(['options' => ['template_class' => false]]);
        $e = new Factory(new ArrayObject(), new ArrayObject(), new API($container));
        $instance1 = $e->factory('one', $class);
        $instance2 = $e->factory('two');
        assertInstanceOf($class, $instance1);
        assertInstanceOf('Foil\Template\Template', $instance2);
    }

    public function testFactorySameInstance()
    {
        $container = new Container(['options' => ['template_class' => false]]);
        $e = new Factory(new ArrayObject(), new ArrayObject(), new API($container));
        $instance1 = $e->factory('one');
        $instance2 = $e->factory('two');
        $instance3 = $e->factory('one');
        assertSame($instance1, $instance3);
        assertNotSame($instance1, $instance2);
    }
}
