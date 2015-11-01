<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Kernel;

use Foil\Tests\TestCase;
use Foil\Kernel\Events;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class EventsTest extends TestCase
{

    public function testFire()
    {
        $e = new Events();
        $e->on('echo', function ($str1, $str2) {
            echo $str1.$str2;
        });
        $this->expectOutputString('Hello!');
        $e->fire('echo', 'Hello', '!');
    }
}
