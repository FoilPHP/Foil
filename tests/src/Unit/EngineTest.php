<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Tests\Unit;

use Foil\Engine;
use Foil\Template\Finder;
use Foil\Tests\TestCase;
use Foil\Kernel\Events;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class EngineTest extends TestCase
{
    /**
     * @param  \Foil\Kernel\Events   $events
     * @param  \Foil\Template\Finder $finder
     * @param  string                $render
     * @return \Foil\Engine
     */
    private function getEngine(Events $events = null, Finder $finder = null, $render = '')
    {
        /** @var \Foil\Template\Stack|\Mockery\MockInterface $stack */
        $stack = Mockery::mock('Foil\Template\Stack');

        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = $finder ?: Mockery::mock('Foil\Template\Finder');
        if (is_null($events)) {
            /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
            $events = Mockery::mock('Foil\Kernel\Events');
            $events
                ->shouldReceive('fire')
                ->with(Mockery::type('string'), Mockery::type('array'))
                ->andReturnNull();
            $events
                ->shouldReceive('on')
                ->with(Mockery::type('string'), Mockery::type('Closure'))
                ->andReturnNull();
        }

        $engine = new Engine($stack, $finder, $events);

        if ($render) {
            /** @var \Foil\Template\Stack|\Mockery\MockInterface $template */
            $template = Mockery::mock('Foil\Template\Template');
            $template
                ->shouldReceive('render')
                ->with(Mockery::type('array'))
                ->andReturn($render);
            $stack
                ->shouldReceive('factory')
                ->with(__FILE__, $engine, Mockery::any())
                ->andReturn($template);

            $events
                ->shouldReceive('fire')
                ->once()
                ->with('f.template.render', $template, Mockery::type('array'))
                ->andReturnNull();
            $events
                ->shouldReceive('fire')
                ->once()
                ->with('f.template.renderered', $template, $render, Mockery::type('int'))
                ->andReturnNull();
        }

        return $engine;
    }

    /**
     * @expectedException \LogicException
     */
    public function testCallFailsIfUnsafeFunction()
    {
        $engine = $this->getEngine();
        $engine->foo();
    }

    public function testCall()
    {
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('fire')
            ->once()
            ->with('f.engine.call', 'useData', [['foo' => 'foo']]);
        $engine = $this->getEngine($events);

        assertSame($engine, $engine->useData(['foo' => 'foo']));
    }

    public function testFireCallOnEvents()
    {
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('fire')
            ->once()
            ->with('foo', 'bar', 'baz');
        $engine = $this->getEngine($events);
        $engine->fire('foo', 'bar', 'baz');
    }

    public function testStatusIdleOnStart()
    {
        $engine = $this->getEngine();
        assertSame(Engine::STATUS_IDLE, $engine->status());
    }

    public function testLoadExtension()
    {
        /** @var \Foil\Contracts\ExtensionInterface $extension */
        $extension = Mockery::mock('Foil\Contracts\ExtensionInterface');
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('fire')
            ->once()
            ->with('f.extension.load', $extension, ['foo' => 'foo'], false);
        $engine = $this->getEngine($events);

        assertSame($engine, $engine->loadExtension($extension, ['foo' => 'foo']));
    }

    public function testRegisterFilter()
    {
        $filter = function () {
            return true;
        };
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('fire')
            ->once()
            ->with('f.filter.register', 'foo', $filter);
        $engine = $this->getEngine($events);

        assertSame($engine, $engine->registerFilter('foo', $filter));
    }

    public function testRegisterFunction()
    {
        $function = function () {
            return true;
        };
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('fire')
            ->once()
            ->with('f.function.register', 'foo', $function, true);
        $engine = $this->getEngine($events);

        assertSame($engine, $engine->registerFunction('foo', $function, true));
    }

    public function testRegisterBlock()
    {
        $block = function () {
            return true;
        };
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('fire')
            ->once()
            ->with('f.block.register', 'foo', $block);
        $engine = $this->getEngine($events);

        assertSame($engine, $engine->registerBlock('foo', $block));
    }

    public function testSetFolders()
    {
        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = Mockery::mock('Foil\Template\Finder');
        $finder
            ->shouldReceive('in')
            ->once()
            ->with(['foo', 'bar'], true)
            ->andReturnNull();
        $engine = $this->getEngine(null, $finder);

        assertSame($engine, $engine->setFolders(['foo', 'bar']));
    }

    public function testAddFolderNoName()
    {
        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = Mockery::mock('Foil\Template\Finder');
        $finder
            ->shouldReceive('in')
            ->once()
            ->with(['foo'])
            ->andReturnNull();
        $engine = $this->getEngine(null, $finder);

        assertSame($engine, $engine->addFolder('foo'));
    }

    public function testAddFolderName()
    {
        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = Mockery::mock('Foil\Template\Finder');
        $finder
            ->shouldReceive('in')
            ->once()
            ->with(['name' => 'foo'])
            ->andReturnNull();
        $engine = $this->getEngine(null, $finder);

        assertSame($engine, $engine->addFolder('foo', 'name'));
    }

    public function testFindCallOnFinder()
    {
        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = Mockery::mock('Foil\Template\Finder');
        $finder
            ->shouldReceive('find')
            ->once()
            ->with('foo')
            ->andReturn('/foo.php');
        $engine = $this->getEngine(null, $finder);

        assertSame('/foo.php', $engine->find('foo', 'meh'));
    }

    public function testRenderFile()
    {
        $engine = $this->getEngine(null, null, 'Rendered!');
        assertSame('Rendered!', $engine->render(__FILE__));
    }

    public function testRender()
    {
        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = Mockery::mock('Foil\Template\Finder');
        $finder
            ->shouldReceive('find')
            ->once()
            ->with('foo')
            ->andReturn(__FILE__);
        $engine = $this->getEngine(null, $finder, 'Rendered!');
        assertSame('Rendered!', $engine->render('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRenderFailsIfTemplateNotFound()
    {
        /** @var \Foil\Template\Finder|\Mockery\MockInterface $finder */
        $finder = Mockery::mock('Foil\Template\Finder');
        $finder
            ->shouldReceive('find')
            ->once()
            ->with('foo')
            ->andReturn(false);
        $engine = $this->getEngine(null, $finder);
        $engine->render('foo');
    }

    public function testRenderTemplate()
    {
        $engine = $this->getEngine(null, null, 'Rendered!');
        assertSame('Rendered!', $engine->renderTemplate(__FILE__));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRenderSectionFailsIfBadSection()
    {
        $engine = $this->getEngine();
        $engine->renderSection('foo', true);
    }

    public function testRenderSectionSingle()
    {
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('on')
            ->once()
            ->with('f.sections.content', Mockery::type('Closure'))
            ->andReturnNull();
        $events
            ->shouldReceive('on')
            ->with(Mockery::type('string'), Mockery::type('Closure'))
            ->andReturnNull();
        $events
            ->shouldReceive('removeListener')
            ->once()
            ->with('f.sections.content', Mockery::type('Closure'))
            ->andReturnNull();

        $engine = $this->getEngine($events, null, 'foo');

        assertSame('', $engine->renderSection(__FILE__, 'foo'));
    }

    public function testRenderSectionMulti()
    {
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('on')
            ->once()
            ->with('f.sections.content', Mockery::type('Closure'))
            ->andReturnNull();
        $events
            ->shouldReceive('on')
            ->with(Mockery::type('string'), Mockery::type('Closure'))
            ->andReturnNull();
        $events
            ->shouldReceive('removeListener')
            ->once()
            ->with('f.sections.content', Mockery::type('Closure'))
            ->andReturnNull();

        $engine = $this->getEngine($events, null, 'foo');

        assertSame(['foo' => '', 'bar' => ''], $engine->renderSection(__FILE__, ['foo', 'bar']));
    }

    public function testRenderSections()
    {
        /** @var \Foil\Kernel\Events|\Mockery\MockInterface $events */
        $events = Mockery::mock('Foil\Kernel\Events');
        $events
            ->shouldReceive('on')
            ->once()
            ->with('f.sections.content', Mockery::type('Closure'))
            ->andReturnNull();
        $events
            ->shouldReceive('on')
            ->with(Mockery::type('string'), Mockery::type('Closure'))
            ->andReturnNull();
        $events
            ->shouldReceive('removeListener')
            ->once()
            ->with('f.sections.content', Mockery::type('Closure'))
            ->andReturnNull();

        $engine = $this->getEngine($events, null, 'foo');

        assertSame([], $engine->renderSections(__FILE__));
    }
}
