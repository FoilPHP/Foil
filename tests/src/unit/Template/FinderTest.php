<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Template;

use Foil\Tests\TestCase;
use Foil\Template\Finder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class FinderTest extends TestCase
{
    private function fooDirs($named = false)
    {
        $base = preg_replace('|[\\/]+|', DIRECTORY_SEPARATOR, FOILTESTSBASEPATH);
        $dirs = [
            $base.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['_files', 'foo']),
            $base.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['_files', 'bar']),
        ];

        return (! $named) ? $dirs : array_combine(['foo', 'bar'], $dirs);
    }

    public function testIn()
    {
        $f1 = new Finder();
        $unnamed_dirs = $this->fooDirs();
        $f1->in($unnamed_dirs);
        $f2 = new Finder();
        $named_dirs = $this->fooDirs(true);
        $f2->in($named_dirs);
        $expected = ['_files.foo' => $unnamed_dirs[0], '_files.bar' => $unnamed_dirs[1]];
        assertSame($expected, $f1->dirs());
        assertSame($named_dirs, $f2->dirs());
    }

    public function testFindInDir()
    {
        $f = new Finder();
        $dirs = $this->fooDirs(true);
        $f->in($dirs);
        $found = $f->find('bar::foo');
        assertSame('foo.php', basename($found));
        assertSame(0, strpos($found, $dirs['bar']));
    }

    public function testFind()
    {
        $f = new Finder();
        $f->in($this->fooDirs());
        assertSame('bar.inc', basename($f->find('bar.inc')));
        assertSame('foo.php', basename($f->find('foo')));
    }

    public function testFindWithExt()
    {
        $f = new Finder('tpl.php');
        $f->in($this->fooDirs());
        assertSame('double.tpl.php', basename($f->find('double')));
    }
}
