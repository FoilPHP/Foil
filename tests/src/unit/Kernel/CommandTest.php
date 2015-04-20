<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Kernel;

use Foil\Tests\TestCase;
use Foil\Kernel\Command;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class CommandTest extends TestCase
{
    public function testFunctionNoEcho()
    {
        $test = function ($str) {
            echo $str;
        };
        $c = new Command();
        $c->registerFunctions(['hello' => $test]);
        assertSame('', $c->run('hello', 'Hello!'));
    }

    public function testFunctionEscape()
    {
        $test = function ($str) {
            return $str;
        };
        $c = new Command();
        $c->registerFunctions(['hello' => $test]);
        $expected = htmlentities('<b>Hello!</b>', ENT_QUOTES, 'UTF-8', false);
        assertSame($expected, $c->run('hello', '<b>Hello!</b>'));
    }

    public function testFunctionNotEscape()
    {
        $test = function ($str) {
            return $str;
        };
        $c = new Command(false);
        $c->registerFunctions(['hello' => $test]);
        assertSame('<b>Hello!</b>', $c->run('hello', '<b>Hello!</b>'));
    }

    public function testPrevendCoreFunctionOverride()
    {
        $test1 = function () {
            return 'A';
        };
        $test2 = function () {
            return 'B';
        };
        $test3 = function () {
            return 'C';
        };
        $test4 = function () {
            return 'D';
        };
        $c = new Command();
        $c->registerFunctions(['t1' => $test1, 't2' => $test2]);
        $c->registerFunctions(['t1' => $test3]); // should override
        $c->lock();
        $c->registerFunctions(['t2' => $test4]); // override shouldn't be possible anymore
        assertSame('C', $c->run('t1'));
        assertSame('B', $c->run('t2'));
    }

    public function testFilters()
    {
        $test = function ($str) {
            return strrev($str);
        };
        $c = new Command();
        $c->registerFilters(['rev' => $test]);
        assertSame('Foo', $c->filter('rev', 'ooF'));
    }
}
