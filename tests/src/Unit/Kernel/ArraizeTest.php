<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Kernel;

use Foil\Tests\TestCase;
use Aura\Html\Escaper\HtmlEscaper;
use Foil\Tests\ToArray;
use Foil\Tests\AsArray;
use Foil\Tests\Json;
use Foil\Tests\Value;
use Foil\Tests\Target;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class ArraizeTest extends TestCase
{

    private function e($var)
    {
        return call_user_func(new HtmlEscaper(), $var);
    }

    public function testScalars()
    {
        assertSame(['<b>foo bar</b>'], \Foil\arraize('<b>foo bar</b>'));
        assertSame([$this->e('<b>foo bar</b>')], \Foil\arraize('<b>foo bar</b>', true));
        assertSame([1], \Foil\arraize(1));
        assertSame([true], \Foil\arraize(true));
        assertSame([(string)1], \Foil\arraize(1, true, [], true));
        assertSame([(string)true], \Foil\arraize(true, true, [], true));
        assertSame([], \Foil\arraize(null));
        assertSame([], \Foil\arraize(false));
        assertSame([], \Foil\arraize(''));
    }

    public function testArray()
    {
        $data = [
            'lev1' => [
                'lev1.1' => '<b>lev1.1</b>',
                'lev1.2' => true,
                'lev1.3' => ['<b>foo</b>', '<b>bar</b>', '<b>baz</b>'],
            ],
            'lev2' => [
                'lev2.2' => [
                    'lev2.2.1' => ['a' => '<b>a</b>', 'b' => '<b>b</b>'],
                    'lev2.2.2' => [1, true, null],
                ],
            ],
        ];
        $esc = [
            'lev1' => [
                'lev1.1' => $this->e('<b>lev1.1</b>'),
                'lev1.2' => true,
                'lev1.3' => [
                    $this->e('<b>foo</b>'),
                    $this->e('<b>bar</b>'),
                    $this->e('<b>baz</b>'),
                ],
            ],
            'lev2' => [
                'lev2.2' => [
                    'lev2.2.1' => ['a' => $this->e('<b>a</b>'), 'b' => $this->e('<b>b</b>')],
                    'lev2.2.2' => [1, true, null],
                ],
            ],
        ];
        $str = [
            'lev1' => [
                'lev1.1' => '<b>lev1.1</b>',
                'lev1.2' => '1',
                'lev1.3' => ['<b>foo</b>', '<b>bar</b>', '<b>baz</b>'],
            ],
            'lev2' => [
                'lev2.2' => [
                    'lev2.2.1' => ['a' => '<b>a</b>', 'b' => '<b>b</b>'],
                    'lev2.2.2' => ['1', '1', ''],
                ],
            ],
        ];
        $escstr = [
            'lev1' => [
                'lev1.1' => $this->e('<b>lev1.1</b>'),
                'lev1.2' => '1',
                'lev1.3' => [
                    $this->e('<b>foo</b>'),
                    $this->e('<b>bar</b>'),
                    $this->e('<b>baz</b>'),
                ],
            ],
            'lev2' => [
                'lev2.2' => [
                    'lev2.2.1' => ['a' => $this->e('<b>a</b>'), 'b' => $this->e('<b>b</b>')],
                    'lev2.2.2' => ['1', '1', ''],
                ],
            ],
        ];
        assertSame($data, \Foil\arraize($data));
        assertSame($esc, \Foil\arraize($data, true));
        assertSame($str, \Foil\arraize($data, false, [], true));
        assertSame($escstr, \Foil\arraize($data, true, [], true));
    }

    public function testStorage()
    {
        $storage = new \SplObjectStorage();
        foreach (range('a', 'c') as $i) {
            $object = new \stdClass();
            $object->$i = "<b>{$i}</b>";
            $storage->attach($object);
        }
        $expected = [
            ['a' => $this->e('<b>a</b>')],
            ['b' => $this->e('<b>b</b>')],
            ['c' => $this->e('<b>c</b>')],
        ];
        assertSame($expected, \Foil\arraize($storage, true));
    }

    public function testMix()
    {
        $storage1 = new \SplObjectStorage();
        $storage1->attach(new \stdClass());
        $storage2 = new \SplObjectStorage();
        $storage2->attach((object)['foo' => '<b>bar</b>']);
        $data = [
            'lev1'     => new \ArrayIterator([
                'lev1.1' => '<b>lev1.1</b>',
                'lev1.2' => true,
                'lev1.3' => ['<b>foo</b>', '<b>bar</b>', '<b>baz</b>'],
            ]),
            'lev2'     => [
                'lev2.2' => [
                    'lev2.2.1' => (object)['a' => '<b>a</b>', 'b' => '<b>b</b>'],
                    'lev2.2.2' => [1, true, null],
                ],
            ],
            'storage1' => $storage1,
            'string',
            33,
            null,
            'storage2' => $storage2,
            true,
        ];
        $expected = [
            'lev1'     => [
                'lev1.1' => $this->e('<b>lev1.1</b>'),
                'lev1.2' => '1',
                'lev1.3' => [
                    $this->e('<b>foo</b>'),
                    $this->e('<b>bar</b>'),
                    $this->e('<b>baz</b>'),
                ],
            ],
            'lev2'     => [
                'lev2.2' => [
                    'lev2.2.1' => ['a' => $this->e('<b>a</b>'), 'b' => $this->e('<b>b</b>')],
                    'lev2.2.2' => ['1', '1', ''],
                ],
            ],
            'storage1' => [[]],
            'string',
            '33',
            '',
            'storage2' => [['foo' => $this->e('<b>bar</b>')]],
            '1',
        ];
        assertSame($expected, \Foil\arraize($data, true, [], true));
    }

    public function testObjectToArray()
    {
        // this file uses contain the object used to test object "arraization"
        // see that file to understand what happen here
        require getenv('FOIL_TESTS_BASEPATH').'/_files/stubs.php';

        $cb = function ($object) {
            return is_object($object) ? ['callbacked' => get_object_vars($object)] : false;
        };
        $trasformers = [
            'Foil\\Tests\\Value'  => 'Foil\\Tests\\Transformer',
            'Foil\\Tests\\Target' => $cb,
        ];
        $storage = new \SplObjectStorage();
        $storage->attach(new \stdClass());
        $data = [
            'to'      => new ToArray('<b>To</b>'),
            'as'      => new AsArray(['as1' => ['as1.1' => ['as1.1.1' => '<b>Deep!</b>']]]),
            'json'    => new Json(),
            'value'   => new Value(['a' => '<b>a</b>', 'b' => '<b>b</b>']),
            'target'  => new Target(['c' => '<b>c</b>', 'd' => '<b>d</b>']),
            'storage' => $storage,
        ];
        $expected = [
            'to'      => [
                'toarray' => [$this->e('<b>To</b>')],
            ],
            'as'      => [
                'asarray' => [
                    'as1' => [
                        'as1.1' => ['as1.1.1' => $this->e('<b>Deep!</b>')],
                    ],
                ],
            ],
            'json'    => [$this->e('<b>I am JSON</b>')],
            'value'   => [
                'transformed' => [
                    'value' => [
                        'a' => $this->e('<b>a</b>'),
                        'b' => $this->e('<b>b</b>'),
                    ],
                ],
            ],
            'target'  => [
                'callbacked' => [
                    'value' => [
                        'c' => $this->e('<b>c</b>'),
                        'd' => $this->e('<b>d</b>'),
                    ],
                ],
            ],
            'storage' => [[]],
        ];
        assertSame($expected, \Foil\arraize($data, true, $trasformers));
    }
}
