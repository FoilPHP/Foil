<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests;

use PHPUnit_Framework_TestCase;
use Brain\Monkey;
use Closure;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Monkey::setUp();
    }

    protected function tearDown()
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    /**
     * @param  callable $closure
     * @param  object   $object
     * @param  array    $args
     * @return mixed
     */
    protected function bindClosure(Closure $closure, $object, array $args = [])
    {
        /** @var \Closure $closure */
        /** @noinspection PhpUndefinedMethodInspection */
        $closure = Closure::bind($closure, $object, get_class($object));

        return call_user_func_array($closure, $args);
    }
}
