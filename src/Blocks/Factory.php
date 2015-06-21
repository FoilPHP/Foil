<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Blocks;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class Factory
{
    const CONTRACT      = '\\Foil\\Contracts\\BlockInterface';
    const DEFAULT_CLASS = '\\Foil\\Blocks\\Block';

    /**
     * @param  callable                       $callback
     * @param  string|null                    $class
     * @return \Foil\Contracts\BlockInterface
     */
    public function factory(callable $callback, $class = null)
    {
        (is_string($class) and is_subclass_of($class, self::CONTRACT)) or $class = self::DEFAULT_CLASS;

        return new $class($callback);
    }
}
