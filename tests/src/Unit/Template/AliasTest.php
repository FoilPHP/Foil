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

use Foil\Template\Alias;
use Foil\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class AliasTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateInvalidNumber()
    {
        new Alias(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateInvalidNumerical()
    {
        new Alias('1');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateInvalidHyphen()
    {
        new Alias('a-a');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateInvalidDot()
    {
        new Alias('a.a');
    }

    public function testToString()
    {
        $this->expectOutputString('foo');
        echo new Alias('foo');
    }

    public function testAsArrayKey()
    {
        $alias = new Alias('bar');
        assertSame(['bar' => $this], ["{$alias}" => $this]);
    }
}
