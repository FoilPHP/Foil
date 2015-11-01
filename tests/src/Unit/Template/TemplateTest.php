<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Template;

use Foil\Tests\TestCase;
use Foil\Template\Template;
use Foil\Template\Alias;
use Mockery;
use ArrayObject;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class TemplateTest extends TestCase
{

    public function testCall()
    {
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $command->shouldReceive('run')->with('foo', 'bar')->once()->andReturn('Foo!');

        $template = new Template('/path', new ArrayObject(), $engine, $command);

        assertSame('Foo!', $template->foo('bar'));
    }

    public function testFilter()
    {
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');

        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $command->shouldReceive('filter')
                ->with(Mockery::type('string'), Mockery::any(), [])
                ->andReturnValues(['foo', 'bar', null, 'baz']);

        $template = new Template('/path', new ArrayObject(), $engine, $command);
        assertSame('baz', $template->filter('first|foo|bar|baz', 'Lorem Ipsum'));
    }

    public function testFilterArgs()
    {
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');

        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $command->shouldReceive('filter')
                ->once()
                ->with('first', 'Lorem Ipsum', ['foo'])
                ->andReturn('Lorem!');
        $command->shouldReceive('filter')
                ->once()
                ->with('last', 'Lorem!', ['bar'])
                ->andReturn('Ipsum!');

        $template = new Template('/path', new ArrayObject(), $engine, $command);

        $filter = $template->filter('first|last', 'Lorem Ipsum', [['foo'], ['bar']]);

        assertSame('Ipsum!', $filter);
    }

    public function testRenderNoLayout()
    {
        // the file foo.php contains the code `echo implode(',', $this->data());`
        $base = realpath(getenv('FOIL_TESTS_BASEPATH')).DIRECTORY_SEPARATOR;
        $path = $base.implode(DIRECTORY_SEPARATOR, ['_files', 'foo', 'foo.php']);

        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');

        $template = new Template($path, new ArrayObject(), $engine, $command);

        $command->shouldReceive('filter')
                ->with(Mockery::type('string'), Mockery::any(), [])
                ->andReturnValues(['foo', 'bar', null, 'baz']);

        $engine->shouldReceive('fire')
               ->with('f.template.prerender', $template)
               ->once()
               ->andReturnNull();

        $engine->shouldReceive('fire')
               ->with('f.template.rendered', $template)
               ->once()
               ->andReturnNull();

        $render = $template->render(['foo', 'bar']);
        assertSame('foo,bar', $render);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLayoutFailsIfBadFile()
    {
        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        $engine->shouldReceive('find')->with('foo')->once()->andReturn(false);
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $template = new Template('/path', new ArrayObject(), $engine, $command);
        $template->layout('foo');
    }

    public function testRenderLayout()
    {
        $base = realpath(getenv('FOIL_TESTS_BASEPATH')).DIRECTORY_SEPARATOR;
        // the file foo.php contains the code `echo implode(',', $this->data());`
        $path = $base.implode(DIRECTORY_SEPARATOR, ['_files', 'foo', 'foo.php']);
        // the file foo.php contains the code `echo implode('|', $this->data());`
        $layout = $base.implode(DIRECTORY_SEPARATOR, ['_files', 'foo', 'bar.inc']);

        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');

        $template = new Template($path, new ArrayObject(), $engine, $command);

        $engine->shouldReceive('fire')
               ->with('f.template.prerender', $template)
               ->once()
               ->andReturnNull();

        $engine->shouldReceive('fire')
               ->with('f.template.layout', $layout, $template)
               ->once()
               ->andReturnNull();

        $engine->shouldReceive('fire')
               ->with('f.template.renderlayout', $layout, $template)
               ->once()
               ->andReturnNull();

        $engine->shouldReceive('fire')
               ->with('f.template.rendered', $template)
               ->once()
               ->andReturnNull();

        $engine->shouldReceive('find')
               ->with('bar.inc')
               ->once()
               ->andReturn($layout);

        $template->layout('bar.inc');
        assertSame('foo|bar', $template->render(['foo', 'bar']));
        assertSame('foo,bar', $template->lastBuffer());
    }

    public function testSupply()
    {
        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $section = Mockery::mock('Foil\Section\Section');
        $section->shouldReceive('content')->once()->andReturn('Ok!');
        $template = new Template('/path', new ArrayObject(['foo' => $section]), $engine, $command);

        assertSame('Ok!', $template->supply('foo'));
    }

    public function testSupplyDefaultString()
    {
        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $template = new Template('/path', new ArrayObject(), $engine, $command);

        assertSame('Ok!', $template->supply('foo', 'Ok!'));
    }

    public function testSupplyDefaultCallable()
    {
        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $template = new Template('/path', new ArrayObject(), $engine, $command);

        assertSame('Ok!', $template->supply('foo', function ($section, $tmpl) use ($template) {
            assertSame('foo', $section);
            assertSame($template, $tmpl);

            return 'Ok!';
        }));
    }

    public function testInsert()
    {
        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');

        $template = new Template('/path', new ArrayObject(), $engine, $command);

        $engine->shouldReceive('fire')
               ->once()
               ->with('f.template.prepartial', 'foo', ['foo' => 'foo'], $template)
               ->andReturnNull();
        $engine->shouldReceive('fire')
               ->once()
               ->with('f.template.afterpartial', $template)
               ->andReturnNull();

        $engine->shouldReceive('render')
               ->once()
               ->with('foo', ['foo' => 'foo'])
               ->andReturn('Ok!');

        assertSame('Ok!', $template->insert('foo', ['foo' => 'foo']));
    }

    public function testInsertIfDoNothingIfFileNotExists()
    {
        /** @var \Foil\Engine|\Mockery\MockInterface $engine */
        $engine = Mockery::mock('Foil\Engine');
        $engine->shouldReceive('find')->with('foo')->once()->andReturn(false);
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $template = new Template('/path', new ArrayObject(), $engine, $command);

        assertSame('', $template->insertif('foo'));
    }

    public function testAlias()
    {
        /** @var \Foil\Engine $engine */
        $engine = Mockery::mock('Foil\Engine');
        /** @var \Foil\Kernel\Command|\Mockery\MockInterface $command */
        $command = Mockery::mock('Foil\Kernel\Command');
        $command->shouldReceive('run')
                ->with('v', 'foo')
                ->andReturn('Foo!');

        $template = new Template('/path', new ArrayObject(), $engine, $command);

        $template->alias(new Alias('T'));

        $file = realpath(getenv('FOIL_TESTS_BASEPATH').'/_files/foo/alias.php');

        $this->bindClosure(function ($file) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->collect($file);
        }, $template, [$file]);

        assertSame('Foo!', $template->buffer());
    }
}
