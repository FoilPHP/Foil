<?php namespace Foil\Tests;

use Foil\API as BaseAPI;
use Pimple\Container;

class API extends BaseAPI
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
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

    public function command()
    {
        return $this->container['command'];
    }

    public function template($path, $class = null)
    {
        return $this->container['template.factory']->factory($path, $class);
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
