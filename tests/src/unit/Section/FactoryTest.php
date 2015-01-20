<?php namespace Foil\Tests\Section;

use Foil\Tests\TestCase;
use Foil\Section\Factory;
use Foil\Contracts\SectionInterface as Section;
use ArrayObject;

class FactoryTest extends TestCase
{
    public function testFactoryNoMode()
    {
        $e = new Factory(new ArrayObject());
        $s_1 = $e->factory('one');
        $s_2 = $e->factory('two');
        $s_3 = $e->factory('one');
        assertInstanceOf('Foil\Section\Section', $s_1);
        assertInstanceOf('Foil\Section\Section', $s_2);
        assertSame($s_1, $s_3);
        assertFalse($s_2 === $s_3);
    }

    public function testFactoryMode()
    {
        $e = new Factory(new ArrayObject());
        $s_1 = $e->factory('one', Section::MODE_APPEND);
        $s_2 = $e->factory('one', Section::MODE_REPLACE);
        $s_3 = $e->factory('two', Section::MODE_APPEND);
        $s_4 = $e->factory('two', Section::MODE_OUTPUT);
        assertSame($s_1, $s_2);
        assertSame(Section::MODE_REPLACE, $s_2->mode());
        assertSame($s_3, $s_4);
        assertSame(Section::MODE_APPEND | Section::MODE_OUTPUT, $s_4->mode());
    }
}
