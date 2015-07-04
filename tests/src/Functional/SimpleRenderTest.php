<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Functional;

use Foil\Tests\TestCaseFunctional;
use Foil\Template\Template;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class SimpleRenderTest extends TestCaseFunctional
{
    /**
     * @coversNothing
     */
    public function testSimpleRender()
    {
        $this->initFoil();
        $render = preg_replace('/[\s]+/', ' ', $this->engine->render('main'));
        assertSame('Hello Alone NO YES', $render);
    }

    /**
     * @coversNothing
     */
    public function testSectionEvent()
    {
        $this->initFoil();
        $section = '';
        /** @var \Foil\Kernel\Events $events */
        $events = $this->container['events'];
        $events->on(
            'f.sections.content',
            function ($name, $content) use (&$section) {
                $name === 'three' and $section = trim(preg_replace('/[\s]+/', ' ', $content));
            }
        );
        $this->expectOutputString('');
        $this->engine->render('second');

        assertSame('YES MAN', $section);
    }

    /**
     * @coversNothing
     */
    public function testLayoutRender()
    {
        $this->initFoil();
        $buffer = '';
        /** @var \Foil\Kernel\Events $events */
        $events = $this->container['events'];
        $events->on(
            'f.template.renderlayout',
            function ($layout, Template $template) use (&$buffer) {
                $buffer = $template->buffer();
            }
        );
        $render = preg_replace('/[\s]+/', ' ', $this->engine->render('second', ['foo' => 'Foo!']));
        assertSame('Hello Bar! World Foo! Alone I Win YES MAN', $render);
        assertSame('Buffalo Bill', trim($buffer));
    }

    /**
     * @coversNothing
     */
    public function testSimpleRenderDoubleExt()
    {
        $this->initFoil(['ext' => 'tpl.php']);
        assertSame('I have 2 extensions', trim($this->engine->render('double')));
    }
}
