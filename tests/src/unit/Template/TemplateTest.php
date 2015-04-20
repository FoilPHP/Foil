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
use Mockery;
use ArrayObject;
use Foil\Template\Template;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class TemplateTest extends TestCase
{
    private function getAPI()
    {
        return Mockery::mock('Foil\API');
    }

    private function getTemplate($path, $api = null)
    {
        $sections = new ArrayObject();
        if (is_null($api)) {
            $api = $this->getAPI();
        }

        return new Template($path, $sections, $api);
    }

    public function testFilter()
    {
        $api = $this->getAPI();
        $api->shouldReceive('foil->filter')
            ->with(Mockery::type('string'), Mockery::any(), [])
            ->andReturnValues(['foo', 'bar', null, 'baz']);
        $template = $this->getTemplate('path', $api);
        assertSame('baz', $template->filter('first|foo|bar|baz', 'Lorem Ipsum'));
    }

    public function testRenderNoLayout()
    {
        $api = $this->getAPI();
        // the file foo.php contains the code `echo implode(',', $this->data());`
        $path = FOILTESTSBASEPATH.implode(DIRECTORY_SEPARATOR, ['', '_files', 'foo', 'foo.php']);
        $template = $this->getTemplate($path, $api);
        $api->shouldReceive('fire')->with('f.template.prerender',
            $template)->once()->andReturnNull();
        $api->shouldReceive('fire')->with('f.template.rendered',
            $template)->once()->andReturnNull();
        $render = $template->render(['foo', 'bar']);
        assertSame('foo,bar', $render);
    }

    public function testRenderLayout()
    {
        $api = $this->getAPI();
        // the file foo.php contains the code `echo implode(',', $this->data());`
        $path = FOILTESTSBASEPATH.implode(DIRECTORY_SEPARATOR, ['', '_files', 'foo', 'foo.php']);
        // the file foo.php contains the code `echo implode('|', $this->data());`
        $layout = FOILTESTSBASEPATH.implode(DIRECTORY_SEPARATOR, ['', '_files', 'foo', 'bar.inc']);
        $template = $this->getTemplate($path, $api);
        $api->shouldReceive('fire')->with('f.template.prerender',
            $template)->once()->andReturnNull();
        $api->shouldReceive('fire')->with('f.template.layout', $layout,
            $template)->once()->andReturnNull();
        $api->shouldReceive('fire')->with('f.template.renderlayout', $layout,
            $template)->once()->andReturnNull();
        $api->shouldReceive('fire')->with('f.template.rendered',
            $template)->once()->andReturnNull();
        $api->shouldReceive('engine->find')->with('bar.inc')->once()->andReturn($layout);
        $template->layout('bar.inc');
        assertSame('foo|bar', $template->render(['foo', 'bar']));
        assertSame('foo,bar', $template->lastBuffer());
    }
}
