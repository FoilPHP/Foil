<?php namespace Foil\Traits;

use Foil\Template\Stack;

/**
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait TemplateAwareTrait
{
    private $stack;

    /**
     * @return Stack
     */
    public function stack()
    {
        return $this->stack;
    }

    public function setStack(Stack $stack)
    {
        $this->stack = $stack;
    }

    /**
     * @return \Foil\Contracts\TemplateInterface
     */
    public function template()
    {
        return $this->stack()->template();
    }
}
