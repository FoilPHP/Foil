<?php namespace Foil\Tests\Template;

use Foil\Tests\TestCase;
use Foil\Template\Factory;
use Foil\API;
use ArrayObject;

class FactoryTest extends TestCase
{
    public function testFactory()
    {
        $e = new Factory(new ArrayObject(), new ArrayObject(), new API());
        $t_1 = $e->factory('one');
        $t_2 = $e->factory('two');
        $t_3 = $e->factory('one');
        assertInstanceOf('Foil\Template\Template', $t_1);
        assertInstanceOf('Foil\Template\Template', $t_2);
        assertSame($t_1, $t_3);
        assertFalse($t_2 === $t_3);
    }
}
