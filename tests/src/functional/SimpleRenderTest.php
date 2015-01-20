<?php namespace Foil\Tests;

class SimpleRenderTest extends TestCaseFunctional
{
    public function testSimpleRender()
    {
        $render = preg_replace('/[\s]+/', ' ', $this->engine->render('main'));
        assertSame('Hello Alone NO YES', $render);
    }

    public function testLayoutRender()
    {
        $buffer = '';
        $this->container['events']->on('f.template.renderlayout', function ($layout, $template) use (&$buffer) {
            $buffer = $template->buffer();
        });
        $render = preg_replace('/[\s]+/', ' ', $this->engine->render('second', ['foo' => 'Foo!']));
        assertSame('Hello Bar! World Foo! Alone I Win YES MAN', $render);
        assertSame('Buffalo Bill', trim($buffer));
    }
}
