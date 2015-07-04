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
use Aura\Html\Escaper\HtmlEscaper;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class CommandTest extends TestCase
{
    /**
     * @return \Foil\Contracts\EscaperInterface $escaper
     */
    private function escaper()
    {
        $escaper = Mockery::mock('Foil\Contracts\EscaperInterface');
        $escaper->shouldReceive('escape')->andReturnUsing(function ($var) {
            if (is_object($var)) {
                return method_exists($var, '__toString')
                    ? (new HtmlEscaper())->__invoke($var->__toString())
                    : '';
            }

            return is_array($var)
                ? array_map(new HtmlEscaper(), $var)
                : (new HtmlEscaper())->__invoke($var);
        });

        return $escaper;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRunFailsIfBadFunctionName()
    {
        $c = new Command($this->escaper());
        $c->run(true);
    }

    /**
     * @expectedException \LogicException
     */
    public function testRunFailsIfBadFunctionNotExists()
    {
        $c = new Command($this->escaper());
        $test = function ($str) {
            echo $str;
        };
        $c->registerFunctions(['hello' => $test]);
        $c->run('goodbye');
    }

    public function testFunctionNoEcho()
    {
        $test = function ($str) {
            echo $str;
        };
        $c = new Command($this->escaper());
        $c->registerFunctions(['hello' => $test]);
        $this->expectOutputString('');
        assertSame('', $c->run('hello', 'Hello!'));
    }

    public function testFunctionObject()
    {
        $test1 = function () {
            return (object) ['foo' => 'bar'];
        };
        $test2 = function () {
            return 'foo';
        };
        $c = new Command($this->escaper());
        $c->registerFunctions(['one' => $test1]);
        $c->registerFunctions(['two' => $test2]);
        assertSame('', $c->run('one'));
        assertSame('foo', $c->run('two'));
    }

    public function testFunctionEcho()
    {
        $test = function ($str) {
            echo $str;
        };
        $c = new Command($this->escaper());
        $c->registerFunctions(['block' => $test]);
        $this->expectOutputString('Hello!');
        $c->run('block', 'Hello!');
    }

    public function testFunctionEscape()
    {
        $test = function ($str) {
            return $str;
        };
        $c = new Command($this->escaper());
        $c->registerFunctions(['hello' => $test]);
        $expected = htmlentities('<b>Hello!</b>', ENT_QUOTES, 'UTF-8', false);
        assertSame($expected, $c->run('hello', '<b>Hello!</b>'));
    }

    public function testFunctionNotEscape()
    {
        $test = function ($str) {
            return $str;
        };
        $c = new Command($this->escaper(), false);
        $c->registerFunctions(['hello' => $test]);
        assertSame('<b>Hello!</b>', $c->run('hello', '<b>Hello!</b>'));
    }

    public function testPreventCoreFunctionOverride()
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
        $c = new Command($this->escaper());
        $c->registerFunctions(['t1' => $test1, 't2' => $test2]);
        $c->registerFunctions(['t1' => $test3]); // should override
        $c->lock();
        $c->registerFunctions(['t2' => $test4]); // override shouldn't be possible anymore
        assertSame('C', $c->run('t1'));
        assertSame('B', $c->run('t2'));
    }

    public function testFunctionsSafe()
    {
        $func = function () {
            return 'A';
        };
        $c = new Command($this->escaper());
        $c->registerFunctions(['a' => $func, 'b' => $func]);
        $c->registerFunctions(['c' => $func, 'd' => $func], ['c']);
        $c->registerFunctions(['e' => $func, 'f' => $func], ['e', 'f']);
        $c->registerFunctions(['g' => $func, 'h' => $func], true);
        assertSame(['c', 'e', 'f', 'g', 'h'], $this->accessPrivateProperty('safe', $c));
    }

    public function testFilters()
    {
        $test = function ($str) {
            return strrev($str);
        };
        $c = new Command($this->escaper());
        $c->registerFilters(['rev' => $test]);
        assertSame('Foo', $c->filter('rev', 'ooF'));
    }
}
