<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Context;

use Foil\Tests\TestCase;
use Mockery;
use SplObjectStorage;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class CollectionTest extends TestCase
{
    private function getCollectionMocked()
    {
        $api = Mockery::mock('Foil\API');
        $api->shouldReceive('fire')->andReturnNull();
        $c = Mockery::mock('Foil\Context\Collection')->makePartial();
        $c->shouldReceive('api')->andReturn($api);
        $c->shouldReceive('data')->once()->withNoArgs()->andReturn(['foo' => 'bar']);

        return $c;
    }

    private function getContextMocked($data, $accept = true)
    {
        $con = Mockery::mock('Foil\Contracts\ContextInterface');
        $con->shouldReceive('accept')->once()->with('foo')->andReturn($accept);
        $con->shouldReceive('provide')->withNoArgs()->andReturn($data);

        return $con;
    }

    public function testProvideReturnDataIfNoTemplate()
    {
        $c = $this->getCollectionMocked();
        assertSame(['foo' => 'bar'], $c->provide());
    }

    public function testProvideNoStorage()
    {
        $c = $this->getCollectionMocked();
        $c->shouldReceive('template')->once()->withNoArgs()->andReturn('foo');
        $c->shouldReceive('storage')->once()->withNoArgs()->andReturn(new SplObjectStorage());
        assertSame(['foo' => 'bar'], $c->provide());
    }

    public function testProvide()
    {
        $con1 = $this->getContextMocked(['one' => 'one']);
        $con2 = $this->getContextMocked(['two' => 'two']);
        $con3 = $this->getContextMocked(['foo' => 'baz']);
        $con4 = $this->getContextMocked(['x' => 'x'], false);
        $storage = new SplObjectStorage();
        $storage->attach($con1);
        $storage->attach($con2);
        $storage->attach($con3);
        $storage->attach($con4);
        $c = $this->getCollectionMocked();
        $c->shouldReceive('template')->once()->withNoArgs()->andReturn('foo');
        $c->shouldReceive('storage')->once()->withNoArgs()->andReturn($storage);
        assertSame(['foo' => 'baz', 'one' => 'one', 'two' => 'two'], $c->provide());
    }
}
