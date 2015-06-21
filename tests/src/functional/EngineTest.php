<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests;

use Foil\Context\SearchContext;
use Foil\Context\RegexContext;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class EngineTest extends TestCaseFunctional
{
    public function setUp()
    {
        parent::setUp();
        $this->initFoil();
    }

    public function testInit()
    {
        // $this->engine is set in `TestCaseFunctional::setUp()`
        assertInstanceOf('Foil\Engine', $this->engine);
    }

    public function testUseData()
    {
        $this->engine->useData(['foo' => 'bar'], 'foo', 'bar');
        // $this->container is set in `TestCaseFunctional::setUp()`
        $context = $this->container['context'];
        $context->accept('foo');
        assertSame(['foo' => 'bar'], $context->provide());
    }

    public function testContext()
    {
        $this->engine
            ->useContext(new SearchContext('foo', ['search_1' => 1]))
            ->useContext(new RegexContext('/^foo/', ['regex_1' => 1]))
            ->useContext('fo', ['search_2' => 2])
            ->useContext('/[\w]+/', ['regex_2' => 2], true)
            ->useContext('no', ['search_3' => 3])
            ->useContext('/[0-9]+/', ['regex_3' => 3], true);
        $context = $this->container['context'];
        $context->accept('foo');
        $expected = [
            'search_1' => 1,
            'regex_1'  => 1,
            'search_2' => 2,
            'regex_2'  => 2,
        ];
        assertSame($expected, $context->provide());
    }

    public function testRegisterExtension()
    {
        $e = Mockery::mock('Foil\Contracts\ExtensionInterface');
        $func = function ($a, $b = '') {
            return $a.$b;
        };
        $filter = function ($a, $args = []) {
            return $a.$args[0];
        };
        $e->shouldReceive('setup')->with([])->andReturnNull();
        $e->shouldReceive('provideFilters')->andReturn(['foo' => $filter]);
        $e->shouldReceive('provideFunctions')->andReturn(['foo' => $func]);
        $this->engine->loadExtension($e);
        assertSame('Hello!', $this->container['command']->run('foo', 'Hello', '!'));
        assertSame('Hello!', $this->container['command']->filter('foo', 'Hello', ['!']));
    }

    public function testRegisterFunction()
    {
        $cb = function ($a, $b = '') {
            echo 'Hello!';

            return $a.$b;
        };
        $this->engine->registerFunction('foo', $cb);
        $expected = htmlentities('<b>Hello</b>', ENT_QUOTES, 'UTF-8', false);
        assertSame($expected, $this->container['command']->run('foo', '<b>Hello', '</b>'));
    }

    public function testRegisterFilter()
    {
        $cb = function ($original, $a, $b) {
            return $original.$a.$b;
        };
        $this->engine->registerFilter('foo', $cb);
        assertSame('Hello!!', $this->container['command']->filter('foo', 'Hello', ['!', '!']));
    }

    public function testRender()
    {
        assertSame('foo,bar', $this->engine->render('foo', ['foo', 'bar']));
    }

    public function testRenderSection()
    {
        $section = $this->engine->renderSection('main', 'three');
        assertSame('YES', trim($section));
    }
}
