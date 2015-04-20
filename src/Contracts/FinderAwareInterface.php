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

use Foil\Template\Finder;

/**
 * Object that "knows" finder service instance (so can solve templates in registered paths).
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface FinderAwareInterface
{
    /**
     * Return the finder instance
     *
     * @return Finder
     */
    public function finder();

    /**
     * Return the finder instance
     *
     * @param  string $template Template name to find
     * @return Finder
     */
    public function find($template);

    /**
     * Set the finder instance
     *
     * @param Finder $finder
     */
    public function setFinder(Finder $finder);
}
