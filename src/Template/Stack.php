<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Template;

use Countable;
use Foil\Engine;
use SplStack;

/**
 * Template stack (essentially a wrap around SplStack) that allow to easily keep track of the
 * template that is being rendered, even in case of with nested templates.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Stack implements Countable
{
    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * @var \Foil\Template\Factory
     */
    private $factory;

    /**
     * @param \Foil\Template\Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->stack = new SplStack();
        $this->factory = $factory;
    }

    /**
     * Factory a template using template helper function and push it to the templates stack
     *
     * @param  string                            $path   Template file full path
     * @param  \Foil\Engine                      $engine
     * @param  string                            $class  Custom template class
     * @return \Foil\Contracts\TemplateInterface
     */
    public function factory($path, Engine $engine, $class = null)
    {
        $template = $this->factory->factory($path, $engine, $class);
        $this->stack->push($template);

        return $template;
    }

    /**
     * Returns the current template in the stack.
     *
     * @return \Foil\Contracts\TemplateInterface
     */
    public function template()
    {
        return $this->stack->top();
    }

    /**
     * Removes a template from the stack and returns it.
     *
     * @return \Foil\Contracts\TemplateInterface
     */
    public function pop()
    {
        return $this->stack->pop();
    }

    /**
     * Returns the count of templates in the stack.
     *
     * @return int
     */
    public function count()
    {
        return $this->stack->count();
    }
}
