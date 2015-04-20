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

/**
 * A context object that composes other context objects so that they can be used as a singular
 * object. It's an implementation of Composite pattern.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface ContextCollectionInterface extends ContextInterface
{
    /**
     * Adds a context to collection
     *
     * @param ContextInterface $context
     */
    public function add(ContextInterface $context);

    /**
     * Remove a context from collection
     *
     * @param ContextInterface $context
     */
    public function remove(ContextInterface $context);

    /**
     * Check if a context is present in the collection
     *
     * @param  ContextInterface $context
     * @return boolean
     */
    public function has(ContextInterface $context);
}
