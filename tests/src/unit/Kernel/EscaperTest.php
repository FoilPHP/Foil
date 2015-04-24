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
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class EscaperTest extends TestCase
{
    private function e($str, $encoding)
    {
        return htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
    }

    private function sut($encoding = 'utf-8')
    {
        /** @var \Aura\Html\Escaper|\Mockery\CompositeExpectation $aura */
        $aura = Mockery::mock('Aura\Html\Escaper');
        $aura
            ->shouldReceive('setEncoding')
            ->atLeast()
            ->once()
            ->with($encoding)
            ->andReturnNull();
        $aura
            ->shouldReceive('html')
            ->atLeast()
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnUsing(function ($str) use ($encoding) {
                return $this->e($str, $encoding);
            });

        return new Escaper($aura, $encoding);
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
        $escaper = $this->sut();
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
        $escaper = $this->sut('iso-8859-1');
        assertSame($expected, $escaper->escape($data));
    }
}
