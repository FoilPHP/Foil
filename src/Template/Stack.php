<?php namespace Foil\Template;

use Countable;
use SplStack;
use Foil\API;
use Foil\Contracts\APIAwareInterface as APIAware;
use Foil\Traits;

/**
 * Template stack (essentially a wrap around SplStack) that allow to easily keep track of the
 * template that is being rendered, even in case of with nested templates.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Stack implements Countable, APIAware
{
    use Traits\APIAwareTrait;

    private $stack;

    public function __construct(API $api)
    {
        $this->stack = new SplStack();
        $this->setAPI($api);
    }

    /**
     * Factory a template using template helper function and push it to the templates stack
     *
     * @param  string                            $path  Template file full path
     * @param  string                            $class Custom template class
     * @return \Foil\Contracts\TemplateInterface
     */
    public function factory($path, $class = null)
    {
        $template = $this->api()->foil('template.factory')->factory($path, $class);
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
