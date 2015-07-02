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

    public function testAlias(){
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

        $collected = '';
        $this->bindClosure(function($file) use(&$collected) {
            /** @noinspection PhpUndefinedMethodInspection */
            $collected = $this->collect($file);
        }, $template, [$file]);

        assertSame('Foo!', trim($collected));
    }
}
