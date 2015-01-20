<?php namespace Foil\Tests\Template;

use Foil\Tests\TestCase;
use Foil\Template\Finder;

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
        assertSame(['_files.foo' => $unnamed_dirs[0], '_files.bar' => $unnamed_dirs[1]], $f1->dirs());
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
}
