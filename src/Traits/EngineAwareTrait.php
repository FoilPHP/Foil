<?php namespace Foil\Traits;

use Foil\Contracts\EngineInterface;

/**
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait EngineAwareTrait
{
    private $engine;

    public function engine()
    {
        return $this->engine;
    }

    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }
}
