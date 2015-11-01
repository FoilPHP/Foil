<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Context;

use Foil\Context\Collection;
use Foil\Tests\TestCase;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class CollectionTest extends TestCase
{

    /**
     * @return \Foil\Context\Collection
     */
    private function getCollection()
    {
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events->shouldReceive('fire')->andReturnNull();
        $collection = new Collection($events);
        $collection->setData(['foo' => 'bar']);

        return $collection;
    }

    /**
     * @param                                                          $data
     * @param  bool                                                    $accept
     * @return \Mockery\MockInterface|\Foil\Contracts\ContextInterface
     */
    private function getContextMocked($data, $accept = true)
    {
        $con = Mockery::mock('Foil\Contracts\ContextInterface');
        $con->shouldReceive('accept')->once()->with('foo')->andReturn($accept);
        $con->shouldReceive('provide')->withNoArgs()->andReturn($data);

        return $con;
    }

    public function testAccept()
    {
        $collection = $this->getCollection();
        assertTrue($collection->accept('template'));
        assertFalse($collection->accept(1));
        $collection->disallow();
        assertFalse($collection->accept('template'));
    }

    public function testAddHasRemove()
    {
        /** @var \Foil\Contracts\ContextInterface $context */
        $context = Mockery::mock('Foil\Contracts\ContextInterface');
        $clone = clone $context;
        $collection = $this->getCollection();
        $collection->add($context);
        assertTrue($collection->has($context));
        assertFalse($collection->has($clone));
        $collection->remove($context);
        assertFalse($collection->has($context));
    }

    public function testProvideReturnDataIfNoTemplate()
    {
        $c = $this->getCollection();
        assertSame(['foo' => 'bar'], $c->provide());
    }

    public function testProvideNoStorage()
    {
        $collection = $this->getCollection();
        $this->setPrivateProperty('template', 'foo', $collection);

        assertSame(['foo' => 'bar'], $collection->provide());
    }

    public function testProvide()
    {
        $con1 = $this->getContextMocked(['one' => 'one']);
        $con2 = $this->getContextMocked(['two' => 'two']);
        $con3 = $this->getContextMocked(['foo' => 'baz']);
        $con4 = $this->getContextMocked(['x' => 'x'], false);
        $collection = $this->getCollection();
        $collection->add($con1);
        $collection->add($con2);
        $collection->add($con3);
        $collection->add($con4);
        $this->bindClosure(function () {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->template = 'foo';
        }, $collection);

        assertSame(['foo' => 'baz', 'one' => 'one', 'two' => 'two'], $collection->provide());
    }
}
