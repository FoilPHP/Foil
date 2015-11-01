<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Section;

use Foil\Tests\TestCase;
use Foil\Section\Factory;
use Foil\Contracts\SectionInterface as Section;
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
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryFailsIfBadName()
    {
        $factory = new Factory(new ArrayObject());
        $factory->factory(true);
    }

    public function testFactoryNoMode()
    {
        $factory = new Factory(new ArrayObject());
        $s_1 = $factory->factory('one');
        $s_2 = $factory->factory('two');
        $s_3 = $factory->factory('one');
        assertInstanceOf('Foil\Section\Section', $s_1);
        assertInstanceOf('Foil\Section\Section', $s_2);
        assertSame($s_1, $s_3);
        assertFalse($s_2 === $s_3);
    }

    public function testFactoryMode()
    {
        $factory = new Factory(new ArrayObject());
        $s_1 = $factory->factory('one', Section::MODE_APPEND);
        $s_2 = $factory->factory('one', Section::MODE_REPLACE);
        $s_3 = $factory->factory('two', Section::MODE_APPEND);
        $s_4 = $factory->factory('two', Section::MODE_OUTPUT);
        assertSame($s_1, $s_2);
        assertSame(Section::MODE_REPLACE, $s_2->mode());
        assertSame($s_3, $s_4);
        assertSame(Section::MODE_APPEND | Section::MODE_OUTPUT, $s_4->mode());
    }

    public function testCustomClass()
    {
        $class = get_class(Mockery::mock(Factory::DEFAULT_CONTRACT));
        $factory = new Factory(new ArrayObject());
        $section = $factory->factory('foo', Section::MODE_APPEND, $class);
        assertInstanceOf($class, $section);
        assertInstanceOf(Factory::DEFAULT_CONTRACT, $section);
        assertNotInstanceOf(Factory::DEFAULT_CLASS, $section);
    }

    public function testDefaultClassIfBadClass()
    {
        $factory = new Factory(new ArrayObject());
        $section = $factory->factory('foo', Section::MODE_APPEND, __CLASS__);
        assertInstanceOf(Factory::DEFAULT_CLASS, $section);
    }
}
