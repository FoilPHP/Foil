<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Tests\Kernel;

use Foil\Tests\TestCase;
use Foil\Kernel\Escaper;
use Aura\Html\Escaper as AuraEscaper;
use Aura\Html\Escaper\HtmlEscaper;
use Aura\Html\Escaper\AttrEscaper;
use Aura\Html\Escaper\CssEscaper;
use Aura\Html\Escaper\JsEscaper;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class EscaperTest extends TestCase
{
    /**
     * @param  mixed  $data
     * @param  string $encoding
     * @param  string $strategy
     * @return string
     */
    private function e($data, $encoding = 'utf-8', $strategy = 'html')
    {
        $aura = $this->auraEscaper();
        $aura->setEncoding($encoding);

        return $aura->$strategy($data);
    }

    /**
     * @return \Aura\Html\Escaper
     */
    private function auraEscaper()
    {
        return new AuraEscaper(
            new HtmlEscaper(),
            new AttrEscaper(new HtmlEscaper()),
            new CssEscaper(),
            new JsEscaper()
        );
    }

    public function testHtmlDefaultStrategy()
    {
        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        assertSame($this->e('<b>Foo</b>'), $escaper->escape('<b>Foo</b>', 'mhe'));
    }

    public function testUtf8()
    {
        $data = [
            'int'    => 1,
            'bool'   => true,
            'string' => '<b>Foo</b>',
            'array'  => [
                'array.string' => '<b>Foo</b>',
                'array.array'  => [
                    'array.array.string' => '<b>Foo</b>',
                    'array.array.array'  => [
                        'array.array.array.string' => '<b>Foo</b>',
                    ],
                ],
            ],
        ];
        $expected = [
            'int'    => 1,
            'bool'   => true,
            'string' => $this->e('<b>Foo</b>', 'utf-8'),
            'array'  => [
                'array.string' => $this->e('<b>Foo</b>', 'utf-8'),
                'array.array'  => [
                    'array.array.string' => $this->e('<b>Foo</b>', 'utf-8'),
                    'array.array.array'  => [
                        'array.array.array.string' => $this->e('<b>Foo</b>', 'utf-8'),
                    ],
                ],
            ],
        ];
        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        assertSame($expected, $escaper->escape($data));
    }

    public function testIso88591()
    {
        $data = [
            'int'    => 1,
            'bool'   => true,
            'string' => '<b>Ñ</b>',
            'array'  => [
                'array.string' => '<b>Ñ</b>',
                'array.array'  => [
                    'array.array.string' => '<b>Ñ</b>',
                    'array.array.array'  => [
                        'array.array.array.string' => '<b>Ñ</b>',
                    ],
                ],
            ],
        ];
        $expected = [
            'int'    => 1,
            'bool'   => true,
            'string' => $this->e('<b>Ñ</b>', 'iso-8859-1'),
            'array'  => [
                'array.string' => $this->e('<b>Ñ</b>', 'iso-8859-1'),
                'array.array'  => [
                    'array.array.string' => $this->e('<b>Ñ</b>', 'iso-8859-1'),
                    'array.array.array'  => [
                        'array.array.array.string' => $this->e('<b>Ñ</b>', 'iso-8859-1'),
                    ],
                ],
            ],
        ];
        $escaper = new Escaper($this->auraEscaper(), 'iso-8859-1');
        assertSame($expected, $escaper->escape($data));
    }

    public function testMultipleEncoding()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM do not yet support multi byte encoding.');
        }
        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        $str = '体字';
        $big5 = $this->e($str, 'big5');
        $utf8 = $this->e($str, 'utf-8');

        assertSame($utf8, $escaper->escape($str));
        assertNotSame($big5, $escaper->escape($str));
        assertSame($big5, $escaper->escape($str, 'html', 'big5'));
    }

    public function testAttr()
    {
        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        $data = ['class' => ['foo', 'bar'], 'id' => 'foo'];
        $expected = 'class="foo bar" id="foo"';

        assertSame($expected, $escaper->escape($data, 'attr'));
    }

    public function testObjectArr()
    {
        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        $data = (object) ['class' => ['foo', 'bar'], 'id' => 'foo'];
        $expected = 'class="foo bar" id="foo"';

        assertSame($expected, $escaper->escape($data, 'attr'));
    }

    public function testObjectHtml()
    {
        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        $data = new \ArrayIterator(['<b>a</b>', '<b>c</b>', '<b>d</b>']);
        $expected = array_map([$this, 'e'], ['<b>a</b>', '<b>c</b>', '<b>d</b>']);

        assertSame($expected, $escaper->escape($data));
    }

    public function testObjectString()
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('__toString')->andReturn('<p>P</p>');

        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        assertSame($this->e('<p>P</p>'), $escaper->escape($mock));
    }

    public function testDecode()
    {
        $str = '<p>à""&àà体</p>';
        $encode = $this->e($str);

        $escaper = new Escaper($this->auraEscaper(), 'utf-8');
        assertSame($str, $escaper->decode($encode));
    }
}
