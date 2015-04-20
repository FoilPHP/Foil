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
 * Object that extends Foil features.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface ExtensionInterface
{
    /**
     * Return an array of filters provided by extension.
     * The array is an hash map of template filters to extension class methods
     *
     * @return array
     */
    public function provideFilters();

    /**
     * Return an array of functions provided by extension.
     * The array is an hash map of template functions to extension class methods
     *
     * @return array
     */
    public function provideFunctions();

    /**
     * Setup the extension using an arguments array that should be provided on registration
     *
     * @param array $args
     */
    public function setup(array $args = []);
}
