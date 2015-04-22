<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Tests\Extension;

use Foil\Tests\TestCase;
use Foil\Extensions\AuraHtml;
use Aura\Html\HelperLocatorFactory;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class AuraHtmlTest extends TestCase
{
    private function sut($register_tag_functions = false)
    {
        return new AuraHtml((new HelperLocatorFactory())->newInstance(), $register_tag_functions);
    }

    public function testHtml()
    {
        $aura = $this->sut();
        $expected = '<a href="http://example.com" class="foo bar">&lt;b&gt;&quot;foo&quot;&lt;/b&gt;</a>';
        $html = $aura->html(
            'anchor',
            'http://example.com',
            '<b>"foo"</b>',
            ['class' => ['foo', 'bar']]
        );
        assertSame($expected, $html);
    }

    public function testHtmlEscapeAttr()
    {
        $aura = $this->sut();
        $expected = '<a href="http://example.com" class="&#039;foo&#039; &quot;bar&quot; &lt;baz&gt;">foo</a>';
        $html = $aura->html(
            'anchor',
            'http://example.com',
            'foo',
            ['class' => ['\'foo\'', '"bar"', '<baz>']]
        );
        assertSame($expected, $html);
    }

    public function testInput()
    {
        $aura = $this->sut();
        $data = [
            'name'    => 'foo',
            'value'   => 'y',
            'attribs' => [
                'label'           => 'Check me',
                'value'           => 'y',
                'value_unchecked' => '0',
            ],
        ];
        $expected =
            '<input type="hidden" value="0" name="foo" /><label>'
            .'<input type="checkbox" name="foo" value="y" checked /> Check me</label>';
        assertSame($expected, trim($aura->html('checkbox', $data)));
    }

    public function testHtmlInput()
    {
        $aura = $this->sut();
        $data = [
            'type'    => 'checkbox',
            'name'    => 'foo',
            'value'   => 'y',
            'attribs' => [
                'label'           => 'Check me',
                'value'           => 'y',
                'value_unchecked' => '0',
            ],
        ];
        $expected =
            '<input type="hidden" value="0" name="foo" /><label>'
            .'<input type="checkbox" name="foo" value="y" checked /> Check me</label>';
        assertSame($expected, trim($aura->html('input', $data)));
    }

    public function testTags()
    {
        $functions = $this->sut(true)->provideFunctions();
        $expected = '<img src="/images/hello.jpg" alt="hello.jpg" id="image-id" />';
        $img = call_user_func($functions['img'], '/images/hello.jpg', ['id' => 'image-id']);
        assertSame($expected, trim($img));
    }
}
