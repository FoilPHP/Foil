<?php namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\EngineAwareInterface;
use Foil\Section\Factory;
use Foil\Contracts\EngineInterface as Engine;
use Foil\Contracts\SectionInterface;
use Foil\Traits;
use SplStack;
use InvalidArgumentException;

/**
 * Extension that provides core features for section block for template inheritance.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Sections implements ExtensionInterface, EngineAwareInterface
{
    use Traits\EngineAwareTrait;

    private $factory;
    private $stack;
    private $names;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
        $this->stack = new SplStack();
        $this->names = new SplStack();
    }

    public function setup(array $args = [])
    {
        return;
    }

    public function provideFilters()
    {
        return [];
    }

    public function provideFunctions()
    {
        return [
            'section' => [$this, 'section'],
            'stop'    => [$this, 'stop'],
            'append'  => [$this, 'append'],
            'replace' => [$this, 'replace']
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
        $this->end($name)->append();
    }

    /**
     * Close a section without output the content.
     * Provide a name is optional, if provided function will check it is the current section.
     *
     * @param string|void $name Section to close
     */
    public function stop($name = null)
    {
        $this->end($name)->stop();
    }

    /**
     * Close a section and replace any content already set for the same section.
     * Provide a name is optional, if provided function will check it is the current section.
     *
     * @param string|void $name Section to close
     */
    public function replace($name = null)
    {
        $this->end($name)->replace();
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
        $current = $this->names->pop();
        if (! is_null($name) && $current !== $name) {
            $n = is_string($name) ? $name : 'a bad';
            $msg = 'You tried to close %s section where you should have closed %s.';
            throw new InvalidArgumentException(sprintf($msg, $n, $current));
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
