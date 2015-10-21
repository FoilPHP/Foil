<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Section;

use Foil\Tests\TestCase;
use Foil\Section\Section;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class SectionTest extends TestCase
{

    /**
     * @expectedException \LogicException
     */
    public function testReplaceFailsIfNoStarted()
    {
        $section = new Section(false);
        $section->replace();
    }

    public function testReplaceNoMode()
    {
        $this->expectOutputString('');
        $section = new Section(false);
        $section->start();
        echo 'Lorem Ipsum Dolor Simet';
        $section->replace();
        assertSame('Lorem Ipsum Dolor Simet', $section->content());
    }

    public function testReplaceModeOutput()
    {
        $this->expectOutputString('Lorem Ipsum Dolor Simet');
        $section = new Section(Section::MODE_REPLACE | Section::MODE_OUTPUT);
        $section->start();
        echo 'Lorem Ipsum Dolor Simet';
        $section->replace();
        assertSame('Lorem Ipsum Dolor Simet', $section->content());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAppendFailsIfNoStarted()
    {
        $section = new Section(false);
        $section->append();
    }

    public function testAppendModeReplace()
    {
        $this->expectOutputString('');
        $section = new Section(Section::MODE_REPLACE);
        $section->start();
        echo 'Lorem Ipsum Dolor Simet';
        $section->append();
        assertSame('', $section->content());
    }

    public function testAppendNoMode()
    {
        $this->expectOutputString('');
        $section = new Section(false);
        $section->start();
        echo 'Lorem Ipsum Dolor Simet';
        $section->append();
        assertSame('Lorem Ipsum Dolor Simet', $section->content());
    }

    public function testAppendModeOutput()
    {
        $this->expectOutputString('Lorem Ipsum Dolor Simet');
        $section = new Section(Section::MODE_APPEND | Section::MODE_OUTPUT);
        $section->start();
        echo 'Lorem Ipsum Dolor Simet';
        $section->append();
        assertSame('Lorem Ipsum Dolor Simet', $section->content());
    }

    public function testStopAsAppend()
    {
        $this->expectOutputString('');
        $section = new Section();
        $section->start();
        echo 'Dolor Simet';
        $section->stop();
        $section->start();
        echo 'Lorem Ipsum ';
        $section->stop();
        assertSame('Lorem Ipsum Dolor Simet', $section->content());
    }

    public function testStopAsReplace()
    {
        $this->expectOutputString('');
        $section = new Section(false, Section::MODE_REPLACE);
        $section->start();
        echo 'Lorem Ipsum';
        $section->stop();
        assertSame(Section::MODE_REPLACE, $section->mode());
        assertSame('Lorem Ipsum', $section->content());
    }
}
