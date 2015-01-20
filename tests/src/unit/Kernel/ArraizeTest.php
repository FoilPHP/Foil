<?php namespace Foil\Tests\Kernel;

use Foil\Tests\TestCase;
use Foil\Kernel\Arraize;
use Foil\Tests\ToArray;
use Foil\Tests\AsArray;
use Foil\Tests\Json;
use Foil\Tests\Value;
use Foil\Tests\Target;

class ArraizeTest extends TestCase
{
    private function arraize($data = [], $escape = false, array $trasformers = [], $tostring = false)
    {
        return (new Arraize())->run($data, $escape, $trasformers, $tostring);
    }

    private function e($var)
    {
        return htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function testScalars()
    {
        assertSame(['<b>foo bar</b>'], $this->arraize('<b>foo bar</b>'));
        assertSame([$this->e('<b>foo bar</b>')], $this->arraize('<b>foo bar</b>', true));
        assertSame([1], $this->arraize(1));
        assertSame([true], $this->arraize(true));
        assertSame([(string) 1], $this->arraize(1, true, [], true));
        assertSame([(string) true], $this->arraize(true, true, [], true));
        assertSame([], $this->arraize(null));
        assertSame([], $this->arraize(false));
        assertSame([], $this->arraize(''));
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
                'lev1.3' => [$this->e('<b>foo</b>'), $this->e('<b>bar</b>'), $this->e('<b>baz</b>')],
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
                'lev1.3' => [$this->e('<b>foo</b>'), $this->e('<b>bar</b>'), $this->e('<b>baz</b>')],
            ],
            'lev2' => [
                'lev2.2' => [
                    'lev2.2.1' => ['a' => $this->e('<b>a</b>'), 'b' => $this->e('<b>b</b>')],
                    'lev2.2.2' => ['1', '1', ''],
                ],
            ],
        ];
        assertSame($data, $this->arraize($data));
        assertSame($esc, $this->arraize($data, true));
        assertSame($str, $this->arraize($data, false, [], true));
        assertSame($escstr, $this->arraize($data, true, [], true));
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
        assertSame($expected, $this->arraize($storage, true));
    }

    public function testMix()
    {
        $storage1 = new \SplObjectStorage();
        $storage1->attach(new \stdClass());
        $storage2 = new \SplObjectStorage();
        $storage2->attach((object) ['foo' => '<b>bar</b>']);
        $data = [
            'lev1'     => new \ArrayIterator([
                'lev1.1' => '<b>lev1.1</b>',
                'lev1.2' => true,
                'lev1.3' => ['<b>foo</b>', '<b>bar</b>', '<b>baz</b>'],
                ]),
            'lev2'     => [
                'lev2.2' => [
                    'lev2.2.1' => (object) ['a' => '<b>a</b>', 'b' => '<b>b</b>'],
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
                'lev1.3' => [$this->e('<b>foo</b>'), $this->e('<b>bar</b>'), $this->e('<b>baz</b>')],
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
        assertSame($expected, $this->arraize($data, true, [], true));
    }

    public function testObjectToArray()
    {
        // this file uses contain the object used to test object "arraization"
        // see that file to understand what happen here
        require FOILTESTSBASEPATH.'/_files/stubs.php';

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
        assertSame($expected, $this->arraize($data, true, $trasformers));
    }
}
