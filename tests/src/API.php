<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests;

use Foil\API as BaseAPI;
use Pimple\Container;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class API extends BaseAPI
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function foil($which = null)
    {
        return $this->container[$which];
    }

    public function engine()
    {
        return $this->container['engine'];
    }

    public function fire()
    {
        call_user_func_array([$this->container['events'], 'fire'], func_get_args());
    }

    public function on($event, callable $callback)
    {
        $this->container['events']->on($event, $callback);
    }

    public function option($which = null)
    {
        return is_null($which) ? $this->container['options'] : $this->container['options'][$which];
    }

    public function run()
    {
        return call_user_func_array([$this->container['command'], 'run'], func_get_args());
    }
}
