<?php namespace Foil\Contracts;

/**
 * Object that "knows" engine service instance.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface EngineAwareInterface
{
    /**
     * Return the engine instance
     *
     * @return EngineInterface
     */
    public function engine();

    /**
     * Set the engine instance
     *
     * @param EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine);
}
