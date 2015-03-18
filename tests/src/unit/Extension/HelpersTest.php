<?php namespace Foil\Tests\Extension;

use Foil\Extensions\Helpers;
use Foil\Tests\TestCase;
use Foil\API;
use Mockery;

class HelpersTest extends TestCase
{
    private function getHelpersMocked($data = [])
    {
        $template = Mockery::mock('Foil\Contracts\TemplateInterface');
        $template->shouldReceive('data')->andReturn($data);
        $template->shouldReceive('filter')->with(Mockery::type('array'), Mockery::any())
                 ->andReturnUsing(function (array $filters, $data) {
                     return $data.' + '.implode(',', $filters);
                 });
        $helpers = Mockery::mock('Foil\Extensions\Helpers')->makePartial();
        $helpers->shouldReceive('template')->withNoArgs()->andReturn($template);
        $helpers->shouldReceive('api')->withNoArgs()->andReturn(new API());

        return $helpers;
    }

    public function testRawDefault()
    {
        $h1 = $this->getHelpersMocked();
        assertSame('foo', $h1->raw('some', 'foo'));
        $h2 = $this->getHelpersMocked(['foo' => 'bar']);
        assertSame('foo', $h2->raw('some', 'foo'));
    }

    public function testRawDefaultClosureEcho()
    {
        $output = $this->getHelpersMocked()->raw('some', function () {
            echo "I'm loving it!";
        });
        assertSame("I'm loving it!", $output);
    }

    public function testRawDefaultClosureReturn()
    {
        $output = $this->getHelpersMocked()->raw('some', function () {
            return "I'm loving it!";
        });
        assertSame("I'm loving it!", $output);
    }

    public function testRaw()
    {
        $h1 = $this->getHelpersMocked(['some' => 'bar']);
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
        $h2 = $this->getHelpersMocked($data);
        assertSame('Deep! + f1,f2', $h2->raw('foo.bar.baz.some|f1|f2', 'foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testStrictVariablesException()
    {
        $helpers = new Helpers(['strict_variables' => true]);
        $helpers->getIn(['foo' => 'bar'], 'bar.baz', true);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testStrictVariablesNotice()
    {
        $helpers = new Helpers(['strict_variables' => 'notice']);
        $helpers->getIn(['foo' => 'bar'], 'bar.baz', true);
    }

    public function testAsArray()
    {
        $helper = $this->getHelpersMocked(['string' => 'foo', 'object' => (object) ['id' => 'foo']]);
        assertSame(['foo'], $helper->asArray('string'));
        assertSame(['id' => 'foo'], $helper->asArray('object'));
    }

    public function testIfNot()
    {
        $helper = $this->getHelpersMocked(['foo' => 'foo', 'bar' => 'bar']);
        assertSame('Yes', $helper->ifNot('baz', 'Yes'));
        assertSame('', $helper->ifNot('foo', 'Yes'));
    }
}
