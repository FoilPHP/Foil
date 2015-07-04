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
use ArrayObject;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class FactoryTest extends TestCase
{
    /**
     * @param  string|bool            $class
     * @return \Foil\Template\Factory
     */
    private function getTemplateFactory($class = false)
    {
        /** @var \Foil\Kernel\Command $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $options = ['template_class' => $class];

        return new Factory(new ArrayObject(), new ArrayObject(), $command, $options);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryFailsIfBadName()
    {
        $factory = $this->getTemplateFactory(false);
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        $factory->factory([], $engine);
    }

    public function testFactoryStandardClass()
    {
        $factory = $this->getTemplateFactory(false);
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        $instance = $factory->factory('one', $engine);
        assertInstanceOf('Foil\Template\Template', $instance);
    }

    public function testFactoryCustomDefaultClass()
    {
        $mock = Mockery::mock('Foil\\Contracts\\TemplateInterface');
        $class = get_class($mock);
        $factory = $this->getTemplateFactory($class);
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        $instance = $factory->factory('one', $engine);
        assertInstanceOf($class, $instance);
    }

    public function testFactoryCustomClass()
    {
        $factory = $this->getTemplateFactory();
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        $mock = Mockery::mock('Foil\\Contracts\\TemplateInterface');
        $class = get_class($mock);
        $instance1 = $factory->factory('one', $engine, $class);
        $instance2 = $factory->factory('two', $engine);
        assertInstanceOf($class, $instance1);
        assertInstanceOf('Foil\Template\Template', $instance2);
    }

    public function testFactorySameInstance()
    {
        $factory = $this->getTemplateFactory();
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        $instance1 = $factory->factory('one', $engine);
        $instance2 = $factory->factory('two', $engine);
        $instance3 = $factory->factory('one', $engine);
        assertSame($instance1, $instance3);
        assertNotSame($instance1, $instance2);
    }
}
