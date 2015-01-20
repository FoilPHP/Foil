<?php namespace Foil\Traits;

use Foil\Template\Finder;

/**
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait FinderAwareTrait
{
    private $finder;

    public function finder()
    {
        return $this->finder;
    }

    public function find($template)
    {
        return $this->finder()->find($template);
    }

    public function setFinder(Finder $finder)
    {
        $this->finder = $finder;
    }
}
