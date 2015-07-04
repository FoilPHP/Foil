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
    /**
     * @param  bool  $named
     * @return array
     */
    private function finderDirectories($named = false)
    {
        $base = realpath(getenv('FOIL_TESTS_BASEPATH'));
        $dirs = [
            $base.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['_files', 'foo']),
            $base.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['_files', 'bar']),
        ];

        return (! $named) ? $dirs : array_combine(['foo', 'bar'], $dirs);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInFailsIfBadDir()
    {
        $finder = new Finder();
        $finder->in(['foo']);
    }

    public function testIn()
    {
        $finder1 = new Finder();
        $unnamed = $this->finderDirectories();
        $finder1->in($unnamed);
        $finder2 = new Finder();
        $named = $this->finderDirectories(true);
        $finder2->in($named);
        $expected = ['_files.foo' => $unnamed[0], '_files.bar' => $unnamed[1]];
        assertSame($expected, $finder1->dirs());
        assertSame($named, $finder2->dirs());
    }

    public function testInEdit()
    {
        $dirs = $this->finderDirectories(true);
        $finder = new Finder();
        $finder->in(['foo' => $dirs['foo']]);
        assertSame(['foo' => $dirs['foo']], $finder->dirs());
        // add
        $finder->in(['bar' => $dirs['bar']]);
        assertSame($dirs, $finder->dirs());
        // edit
        $finder->in(['bar' => $dirs['foo']]);
        assertSame(['foo' => $dirs['foo'], 'bar' => $dirs['foo']], $finder->dirs());
        $finder->in(['foo' => $dirs['foo']]); // this do nothing
        assertSame(['foo' => $dirs['foo'], 'bar' => $dirs['foo']], $finder->dirs());
        // reset
        $finder->in(['foo' => $dirs['foo']], true);
        assertSame(['foo' => $dirs['foo']], $finder->dirs());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFindFailsIfEmptyTemplate()
    {
        $finder = new Finder();
        $finder->find([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFindFailsIfBadTemplate()
    {
        $finder = new Finder();
        $finder->find(true);
    }

    public function testFindInDir()
    {
        $finder = new Finder();
        $dirs = $this->finderDirectories(true);
        $finder->in($dirs);
        $found = $finder->find('bar::foo');
        assertSame('foo.php', basename($found));
        assertSame(0, strpos($found, $dirs['bar']));
    }

    public function testFindInDirFalseIfNoTemplate()
    {
        $finder = new Finder();
        $dirs = $this->finderDirectories(true);
        $finder->in($dirs);
        assertFalse($finder->find('foo::second'));
    }

    public function testFind()
    {
        $finder = new Finder();
        $finder->in($this->finderDirectories());
        assertSame('bar.inc', basename($finder->find('bar.inc')));
        assertSame('foo.php', basename($finder->find('foo')));
    }

    public function testFindWithExt()
    {
        $finder = new Finder('tpl.php');
        $finder->in($this->finderDirectories());
        assertSame('double.tpl.php', basename($finder->find('double')));
    }

    public function testFindMany()
    {
        $finder = new Finder();
        $dirs = $this->finderDirectories(true);
        $finder->in($dirs);
        assertSame('foo.php', basename($finder->find(['mhe', 'nope', 'foo'])));
    }

    public function testFindManyInDirs()
    {
        $finder = new Finder();
        $dirs = $this->finderDirectories(true);
        $finder->in($dirs);
        $found = $finder->find(['foo::mhe', 'foo::second', 'bar::second']);
        assertSame('second.php', basename($found));
        assertSame('bar', basename(dirname($found)));
    }
}
