<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\EngineAwareInterface;
use Foil\Kernel\Events;
use Foil\Section\Factory;
use Foil\Contracts\EngineInterface as Engine;
use Foil\Contracts\SectionInterface;
use Foil\Traits;
use SplStack;
use InvalidArgumentException;

/**
 * Extension that provides core features for section block for template inheritance.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
class Sections implements ExtensionInterface, EngineAwareInterface
{
    use Traits\EngineAwareTrait;

    /**
     * @var \Foil\Section\Factory
     */
    private $factory;

    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * @var \SplStack
     */
    private $names;

    /**
     * @var string
     */
    private $last = null;

    /**
     * @var \Foil\Kernel\Events
     */
    private $events;

    /**
     * @param \Foil\Section\Factory $factory
     * @param \Foil\Kernel\Events   $events
     */
    public function __construct(Factory $factory, Events $events)
    {
        $this->factory = $factory;
        $this->stack = new SplStack();
        $this->names = new SplStack();
        $this->events = $events;
    }

    /**
     * @inheritdoc
     */
    public function setup(array $args = [])
    {
        $this->events->on('f.renderered', function () {
            $this->stack = new SplStack();
            $this->names = new SplStack();
            $this->factory->flush();
        });

        return;
    }

    /**
     * @inheritdoc
     */
    public function provideFilters()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function provideFunctions()
    {
        return [
            'section'      => [$this, 'section'],
            'stop'         => [$this, 'stop'],
            'append'       => [$this, 'append'],
            'replace'      => [$this, 'replace'],
            'clear'        => [$this, 'clear']
        ];
    }

    /**
     * Open a section. Optionally is possible to use a custom section class.
     *
     * @param string $name  Section id
     * @param string $class Custom class name
     */
    public function section($name, $class = null)
    {
        /** @var \Foil\Contracts\SectionInterface $section */
        $section = $this->factory->factory($name, $this->mode(), $class);
        $section->start();
        $this->stack->push($section);
        $this->names->push($name);
    }

    /**
     * Close a section without output the content.
     * Provide a name is optional, if provided function will check it is the current section.
     *
     * @param string|void $name Section to close
     */
    public function append($name = null)
    {
        $section = $this->end($name);
        $section->append();
        $this->events->fire('f.sections.content', $this->last, $section->content());
    }

    /**
     * Close a section without output the content.
     * Provide a name is optional, if provided function will check it is the current section.
     *
     * @param string|void $name Section to close
     */
    public function stop($name = null)
    {
        $section = $this->end($name);
        $section->stop();
        $this->events->fire('f.sections.content', $this->last, $section->content());
    }

    /**
     * Close a section and replace any content already set for the same section.
     * Provide a name is optional, if provided function will check it is the current section.
     *
     * @param string|void $name Section to close
     */
    public function replace($name = null)
    {
        $section = $this->end($name);
        $section->replace();
        $this->events->fire('f.sections.content', $this->last, $section->content());
    }

    /**
     * Empty a section content.
     * Useful when a layout defines a section that is not wanted on template.
     *
     * @param string|null $name
     */
    public function clear($name = null)
    {
        $this->section($name);
        $this->replace($name);
    }

    /**
     * Ends a section. If a name is provided checks that the name provided is the right one.
     *
     * @param string|void Section to close
     * @return \Foil\Contracts\SectionInterface
     * @throws InvalidArgumentException         If provided name is not the section to be closed.
     */
    private function end($name)
    {
        $this->last = $this->names->pop();
        if (! is_null($name) && $this->last !== $name) {
            $n = is_string($name) ? $name : 'a bad';
            $msg = 'You tried to close %s section where you should have closed %s.';
            throw new InvalidArgumentException(sprintf($msg, $n, $this->last));
        }

        return $this->stack->pop();
    }

    /**
     * Return section output mode constant or false, based on engine status.
     *
     * @return int|boolean
     * @access private
     */
    private function mode()
    {
        $status = $this->engine()->status();
        if ($status & Engine::STATUS_IN_LAYOUT || $status & Engine::STATUS_IN_PARTIAL) {
            return SectionInterface::MODE_OUTPUT;
        }

        return false;
    }
}
