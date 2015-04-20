<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Contracts;

use Foil\Template\Stack;

/**
 * Object that "knows" template stack service instance and can access the template object
 * is being rendered.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface TemplateAwareInterface
{
    /**
     * Return the template manager instance
     *
     * @return Stack
     */
    public function stack();

    /**
     * Return current template instance
     *
     * @return \Foil\Template\Template
     */
    public function template();

    /**
     * Set the template manager instance
     *
     * @param Stack $stack
     */
    public function setStack(Stack $stack);
}
