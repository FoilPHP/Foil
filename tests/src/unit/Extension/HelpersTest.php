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
     * @param  array                    $data
     * @param  bool|string              $strict
     * @return \Foil\Extensions\Helpers
     */
    private function getHelpers($data = [], $strict = false)
    {
        /** @var \Foil\Contracts\TemplateInterface|\Mockery\MockInterface $template */
        $template = Mockery::mock('Foil\Contracts\TemplateInterface');
        $template->shouldReceive('data')->andReturn($data);
        $template->shouldReceive('filter')->with(Mockery::type('array'), Mockery::any())
                 ->andReturnUsing(function (array $filters, $data) {
                     return $data.' + '.implode(',', $filters);
                 });

        /** @var \Foil\Template\Stack|\Mockery\MockInterface $stack */
        $stack =  Mockery::mock('\Foil\Template\Stack');
        $stack->shouldReceive('template')->andReturn($template);

        /** @var \Foil\Kernel\Escaper|\Mockery\MockInterface $escaper */
        $escaper =  Mockery::mock('\Foil\Kernel\Escaper');
        $escaper->shouldReceive('escape')->andReturnUsing(function ($data) {
            if (is_string($data)) {
                return htmlentities($data);
            } elseif (is_array($data)) {
                return array_map('htmlentities', $data);
            }

            return $data;
        });

        $helpers = new Helpers($escaper, ['autoescape' => true, 'strict_variables' => $strict]);
        $helpers->setStack($stack);

        return $helpers;
    }

    public function testRawDefault()
    {
        $h1 = $this->getHelpers();
        assertSame('foo', $h1->raw('some', 'foo'));
        $h2 = $this->getHelpers(['foo' => 'bar']);
        assertSame('foo', $h2->raw('some', 'foo'));
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
        $h1 = $this->getHelpers(['some' => 'bar']);
        assertSame('bar', $h1->raw('some', 'foo'));
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => [
                        'some' => 'Deep!',
                    ],
                ],
            ],
        ];
        $h2 = $this->getHelpers($data);
        assertSame('Deep! + f1,f2', $h2->raw('foo.bar.baz.some|f1|f2', 'foo'));
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
        $helper = $this->getHelpers(['string' => 'foo', 'object' => (object) ['id' => 'foo']]);
        assertSame(['foo'], $helper->asArray('string'));
        assertSame(['id' => 'foo'], $helper->asArray('object'));
    }

    public function testIfNot()
    {
        $helper = $this->getHelpers(['foo' => 'foo', 'bar' => 'bar']);
        assertSame('Yes', $helper->ifNot('baz', 'Yes'));
        assertSame('', $helper->ifNot('foo', 'Yes'));
    }
}
