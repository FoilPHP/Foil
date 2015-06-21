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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class AdvRenderTest extends TestCaseFunctional
{
    private $templates_path;

    public function setUp()
    {
        parent::setUp();
        $this->initFoil();
        $base = realpath(getenv('FOIL_TESTS_BASEPATH')).DIRECTORY_SEPARATOR;
        $this->templates_path = $base.implode(DIRECTORY_SEPARATOR, ['_files', 'templates']);

        $this->engine->addFolder($this->templates_path);
    }

    public function testRender()
    {
        $this->registerCallbacks();
        $this->provideGlobalContext();
        $this->provideSearchContext();
        $this->provideRegexContext();
        $expected = file_get_contents($this->templates_path.'/expected.html');
        $render = $this->engine->render('final-template', ['test_me' => 'TEST ME!']);
        assertSame($this->normalize($expected), $this->normalize($render));
    }

    private function registerCallbacks()
    {
        $this->engine->registerFunction('returnSomething', function () {
            return 'Here something for you.';
        });
        $this->engine->registerFilter('uppercase', function ($string) {
            return strtoupper($string);
        });
        $this->engine->registerFilter('reverse', function ($string) {
            return strrev($string);
        });
    }

    private function provideGlobalContext()
    {
        $this->engine->useData([
            'title' => 'Global Title', // should be overridded
            'menu'  => [
                ['http://www.example.com', 'First Item'],
                ['#', 'Second Item'],
            ],
        ]);
    }

    private function provideSearchContext()
    {
        $context = new SearchContext('final', [
            'a' => [
                'var'    => [
                    '!dlrow olleh',
                    'in' => [
                        'extended' => 'Another deep var.',
                    ],
                ],
                'pretty' => [
                    'deep' => [
                        'var' => 'Lorem Ipsum Dolor',
                    ],
                ],
            ]
        ]);
        $context_failed = new SearchContext('foo', ['i_do_not_exist' => 'NOT SHOULD BE HERE']);
        $this->api->addContextUsing($context);
        $this->api->addContextUsing($context_failed);
        $this->engine->useContext('final', ['lowercase' => 'esacrewol ma i']);
    }

    private function provideRegexContext()
    {
        $context = new RegexContext('/-template\.php/');
        $context->setData([
            'html_content' => '<strong>This is a strong tag html content</strong>',
        ]);
        $context_failed = new RegexContext('/[0-9]+/', ['i_do_not_exist' => 'NOT SHOULD BE HERE']);
        $this->engine->useContext($context);
        $this->engine->useContext($context_failed);
        $this->engine->useContext('/\.php$/', ['title' => 'Foil is Awesome!', ], true);
    }

    /**
     * @param $html
     * @return string
     */
    private function normalize($html)
    {
        return preg_replace('~[\s]+~s', '', $html);
    }
}
