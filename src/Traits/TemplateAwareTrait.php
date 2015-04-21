<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Traits;

use Foil\Template\Stack;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait TemplateAwareTrait
{
    /**
     * @var \Foil\Template\Stack
     */
    private $stack;

    /**
     * @return Stack
     */
    public function stack()
    {
        return $this->stack;
    }

    /**
     * @param \Foil\Template\Stack $stack
     */
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
