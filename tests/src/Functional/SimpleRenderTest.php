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
        $render = trim( preg_replace('/[\s]+/', ' ', $this->engine->render('main')) );
        assertSame('Hello Alone NO YES', $render);
    }

    /**
     * @coversNothing
     */
    public function testSimpleRenderMoreTimes()
    {
        $this->initFoil();
        $render1 = preg_replace('/[\s]+/', ' ', $this->engine->render('main'));
        $render2 = preg_replace('/[\s]+/', ' ', $this->engine->render('main'));
        $render3 = preg_replace('/[\s]+/', ' ', $this->engine->render('main'));
        assertSame('Hello Alone NO YES', trim( $render1 ));
        assertSame($render1, $render2);
        assertSame($render2, $render3);
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
        $render1 = preg_replace('/[\s]+/', ' ', $this->engine->render('second', ['foo' => 'Foo!']));
        $render2 = preg_replace('/[\s]+/', ' ', $this->engine->render('second', ['foo' => 'Foo!']));
        assertSame('Hello Bar! World Foo! Alone I Win YES MAN', trim($render1));
        assertSame($render1, $render2);
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

    /**
     * @coversNothing
     */
    public function testClearSection()
    {
        $this->initFoil();
        $render = trim(preg_replace('/[\s]+/', ' ', $this->engine->render('clear')));
        assertSame('Alone YES', trim($render));
    }
}
