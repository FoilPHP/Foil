<?php namespace Foil\Tests\Section;

use Foil\Tests\TestCase;
use Foil\Section\Section;

class SectionTest extends TestCase
{
    public function testReplaceNoMode()
    {
        $this->expectOutputString('');
        $s = new Section(false);
        $s->start();
        echo 'Lorem Ipsum Dolor Simet';
        $s->replace();
        assertSame('Lorem Ipsum Dolor Simet', $s->content());
    }

    public function testReplaceModeOutput()
    {
        $this->expectOutputString('Lorem Ipsum Dolor Simet');
        $s = new Section(Section::MODE_REPLACE | Section::MODE_OUTPUT);
        $s->start();
        echo 'Lorem Ipsum Dolor Simet';
        $s->replace();
        assertSame('Lorem Ipsum Dolor Simet', $s->content());
    }

    public function testAppendModeReplace()
    {
        $this->expectOutputString('');
        $s = new Section(Section::MODE_REPLACE);
        $s->start();
        echo 'Lorem Ipsum Dolor Simet';
        $s->append();
        assertSame('', $s->content());
    }

    public function testAppendNoMode()
    {
        $this->expectOutputString('');
        $s = new Section(false);
        $s->start();
        echo 'Lorem Ipsum Dolor Simet';
        $s->append();
        assertSame('Lorem Ipsum Dolor Simet', $s->content());
    }

    public function testAppendModeOutput()
    {
        $this->expectOutputString('Lorem Ipsum Dolor Simet');
        $s = new Section(Section::MODE_APPEND | Section::MODE_OUTPUT);
        $s->start();
        echo 'Lorem Ipsum Dolor Simet';
        $s->append();
        assertSame('Lorem Ipsum Dolor Simet', $s->content());
    }

    public function testStopAsAppend()
    {
        $this->expectOutputString('');
        $s = new Section();
        $s->start();
        echo 'Dolor Simet';
        $s->stop();
        $s->start();
        echo 'Lorem Ipsum ';
        $s->stop();
        assertSame('Lorem Ipsum Dolor Simet', $s->content());
    }

    public function testStopAsRemplate()
    {
        $this->expectOutputString('');
        $s = new Section(false, Section::MODE_REPLACE);
        $s->start();
        echo 'Lorem Ipsum';
        $s->stop();
        assertSame(Section::MODE_REPLACE, $s->mode());
        assertSame('Lorem Ipsum', $s->content());
    }
}
