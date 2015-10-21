<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Extension;

use Aura\Html\Escaper\AttrEscaper;
use Aura\Html\Escaper\HtmlEscaper;
use Foil\Tests\TestCase;
use Foil\Extensions\Helpers;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class HelpersTest extends TestCase
{

    /**
     * @param  array       $data
     * @param  bool|string $strict
     * @param  bool        $autoescape
     * @return \Foil\Extensions\Helpers
     */
    private function getHelpers($data = [], $strict = false, $autoescape = true)
    {
        /** @var \Foil\Contracts\TemplateInterface|\Mockery\MockInterface $template */
        $template = Mockery::mock('Foil\Contracts\TemplateInterface');
        $template->shouldReceive('data')->andReturn($data);
        $template
            ->shouldReceive('filter')
            ->with(Mockery::type('array'), Mockery::any())
            ->andReturnUsing(function (array $filters, $data) {
                return $data.' + '.implode(',', $filters);
            });

        /** @var \Foil\Template\Stack|\Mockery\MockInterface $stack */
        $stack = Mockery::mock('\Foil\Template\Stack');
        $stack->shouldReceive('template')->andReturn($template);

        /** @var \Foil\Kernel\Escaper|\Mockery\MockInterface $escaper */
        $escaper = Mockery::mock('\Foil\Kernel\Escaper');
        $escaper
            ->shouldReceive('escape')
            ->with(Mockery::any(), Mockery::anyOf('html', 'js', 'css'), Mockery::any())
            ->andReturnUsing(function ($data) {
                if (is_string($data)) {
                    return (new HtmlEscaper())->__invoke($data);
                } elseif (is_array($data)) {
                    return array_map(new HtmlEscaper(), $data);
                }

                return $data;
            });
        $escaper
            ->shouldReceive('escape')
            ->with(Mockery::any(), 'attr', Mockery::any())
            ->andReturnUsing(function ($data) {
                return (new AttrEscaper(new HtmlEscaper()))->__invoke($data);
            });
        $escaper
            ->shouldReceive('decode')
            ->andReturnUsing(function ($data) {
                if (is_string($data)) {
                    return html_entity_decode($data);
                } elseif (is_array($data)) {
                    return array_map('html_entity_decode', $data);
                }

                return $data;
            });

        $helpers = new Helpers(
            $escaper,
            [
                'autoescape'       => $autoescape,
                'strict_variables' => $strict
            ]
        );
        $helpers->setStack($stack);

        return $helpers;
    }

    public function testProvideFilters()
    {
        $helpers = $this->getHelpers();
        $filters = $helpers->provideFilters();
        foreach ($filters as $filter) {
            assertInternalType('callable', $filter);
        }
    }

    public function testProvideFunctions()
    {
        $helpers = $this->getHelpers();
        $filters = $helpers->provideFunctions();
        foreach ($filters as $filter) {
            assertInternalType('callable', $filter);
        }
    }

    public function testVariableEscape()
    {
        $helpers = $this->getHelpers(['foo' => '<p>foo</p>']);
        assertSame(htmlentities('<p>foo</p>'), $helpers->variable('foo'));
    }

    public function testVariableNoEscape()
    {
        $helpers = $this->getHelpers(['foo' => '<p>foo</p>'], true, false);
        assertSame('<p>foo</p>', $helpers->variable('foo'));
    }

    public function testEscape()
    {
        $helpers = $this->getHelpers(['foo' => '<p>foo</p>']);
        assertSame(htmlentities('<p>foo</p>'), $helpers->escape('foo'));
        assertSame(htmlentities('<p>foo</p>'), $helpers->escape('bar', '<p>foo</p>'));
    }

    public function testEscapeJs()
    {
        $helpers = $this->getHelpers(['foo' => '<p>foo</p>']);
        assertSame(htmlentities('<p>foo</p>'), $helpers->escapeJs('foo'));
        assertSame(htmlentities('<p>foo</p>'), $helpers->escapeJs('bar', '<p>foo</p>'));
    }

    public function testEscapeCss()
    {
        $helpers = $this->getHelpers(['foo' => '<p>foo</p>']);
        assertSame(htmlentities('<p>foo</p>'), $helpers->escapeCss('foo'));
        assertSame(htmlentities('<p>foo</p>'), $helpers->escapeCss('bar', '<p>foo</p>'));
    }

    public function testEscapeAttr()
    {
        $helpers = $this->getHelpers(['foo' => ['class' => ['fo<o', 'bar']]]);
        assertSame('class="fo&lt;o bar"', $helpers->escapeAttr('foo'));
    }

    public function testDecode()
    {
        $helpers = $this->getHelpers(['foo' => htmlentities('<p>foo</p>')]);
        assertSame('<p>foo</p>', $helpers->decode('foo'));
    }

    public function testEntities()
    {
        $helpers = $this->getHelpers();
        assertSame(htmlentities('<p>foo</p>'), $helpers->entities('<p>foo</p>'));
    }

    public function testDecodeEntities()
    {
        $helpers = $this->getHelpers();
        assertSame('<p>foo</p>', $helpers->decodeEntities(htmlentities('<p>foo</p>')));
    }

    public function testRawDefault()
    {
        $helpers1 = $this->getHelpers();
        assertSame('foo', $helpers1->raw('some', 'foo'));
        $helpers2 = $this->getHelpers(['foo' => 'bar']);
        assertSame('foo', $helpers2->raw('some', 'foo'));
    }

    public function testRawDefaultClosureEcho()
    {
        $output = $this->getHelpers()->raw('some', function () {
            echo "I'm loving it!";
        });
        assertSame("I'm loving it!", $output);
    }

    public function testRawDefaultClosureReturn()
    {
        $output = $this->getHelpers()->raw('some', function () {
            return "I'm loving it!";
        });
        assertSame("I'm loving it!", $output);
    }

    public function testRaw()
    {
        $helpers1 = $this->getHelpers(['some' => 'bar']);
        assertSame('bar', $helpers1->raw('some', 'foo'));
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => [
                        'some' => 'Deep!',
                    ],
                ],
            ],
        ];
        $helpers2 = $this->getHelpers($data);
        assertSame('Deep! + f1,f2', $helpers2->raw('foo.bar.baz.some|f1|f2', 'foo'));
    }

    public function testRawFilters()
    {
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => [
                        'some' => 'Deep!',
                    ],
                ],
            ],
        ];
        $helpers = $this->getHelpers($data);
        assertSame('Deep! + f1,f2', $helpers->raw('foo.bar.baz.some', 'foo', 'f1|f2'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testStrictVariablesException()
    {
        $helpers = $this->getHelpers([], true);
        $helpers->getIn(['foo' => 'bar'], 'bar.baz', true);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testStrictVariablesNotice()
    {
        $helpers = $this->getHelpers([], 'notice');
        $helpers->getIn(['foo' => 'bar'], 'bar.baz', true);
    }

    public function testAsArray()
    {
        $helpers = $this->getHelpers([
            'string' => '<p>foo</p>',
            'object' => (object)['id' => 'foo'],
        ]);
        assertSame([htmlentities('<p>foo</p>')], $helpers->asArray('string'));
        assertSame(['id' => 'foo'], $helpers->asArray('object'));
    }

    public function testAsArrayRaw()
    {
        $helpers = $this->getHelpers(['object' => (object)['id' => '<p>foo</p>']]);
        assertSame(['id' => '<p>foo</p>'], $helpers->asArrayRaw('object'));
    }

    public function testIfNot()
    {
        $helpers = $this->getHelpers(['foo' => 'foo', 'bar' => 'bar']);
        assertSame('Yes', $helpers->ifNot('baz', 'Yes'));
        assertSame('', $helpers->ifNot('foo', 'Yes'));
    }
}
