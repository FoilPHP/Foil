<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Kernel;

use Evenement;

/**
 * A basic implementation of Evenement, a very time event emitter package.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Events implements Evenement\EventEmitterInterface
{
    use Evenement\EventEmitterTrait;

    /**
     * Emit an event passing with support for variadic arguments
     *
     * @param string $event
     */
    public function fire($event)
    {
        $this->emit($event, array_slice(func_get_args(), 1));
    }
}
