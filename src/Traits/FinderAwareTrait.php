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

use Foil\Template\Finder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
trait FinderAwareTrait
{
    /**
     * @var \Foil\Template\Finder
     */
    private $finder;

    /**
     * @return Finder
     */
    public function finder()
    {
        return $this->finder;
    }

    /**
     * @param  string      $template
     * @return bool|string
     */
    public function find($template)
    {
        return $this->finder()->find($template);
    }

    /**
     * @param \Foil\Template\Finder $finder
     */
    public function setFinder(Finder $finder)
    {
        $this->finder = $finder;
    }
}
