<?php namespace Foil\Tests\Kernel;

use Foil\Tests\TestCase;
use Foil\Kernel\Events;

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
