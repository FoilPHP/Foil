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

use Foil\Contracts\EngineInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
trait EngineAwareTrait
{
    /**
     * @var \Foil\Contracts\EngineInterface
     */
    private $engine;

    /**
     * @return EngineInterface
     */
    public function engine()
    {
        return $this->engine;
    }

    /**
     * @param \Foil\Contracts\EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }
}
