<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * This file contain API function that can be used to run packages tasks without dealing with
 * internal objects, container and so on.
 *
 * After Engine has been created, via the `Foil\engine()` function, all the other functions
 * in this file can be also accessed via API class.
 * E.g. is possible to do
 *
 * `$api = new Foil\API();`
 * `$api->fire($event);`
 *
 * This allows to easily integrate functions in OOP projects (and mock it in tests).
 *
 * Functions in this file are snake_cased, but when using API is possible to call them using
 * camelCase, e.g. the function `Foil\add_global_context($data)` can be called using
 * `$api->addGlobalContext($data)`.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Foil;

use Foil\Contracts\ContextInterface;
use LogicException;
use InvalidArgumentException;

if (! function_exists('Foil\foil')) {
    /**
     * On first call instantiate the container (Pimple) and register service providers.
     * On subsequent calls returns container or a service whose id has been passed as argument.
     *
     * @staticvar \Pimple\Container     $container
     * @param  string|void     $which     Service id
     * @param  array           $options   Engine options
     * @param  array           $providers Custom service provider classes
     * @return \Foil\API|mixed API object or the service whose id has been passed in $which
     * @deprecated
     */
    function foil($which = null, array $options = [], array $providers = [])
    {
        static $app = null;
        if (is_null($app) && $which !== 'engine') {
            throw new LogicException('Engine must be instantiated before to retrieve any service.');
        } elseif (is_null($app)) {
            $app = Foil::boot($options, $providers);
        } elseif (! is_null($which) && ! is_string($which)) {
            throw new InvalidArgumentException('Service name must be in a string.');
        }

        return is_null($which) ? $app->api() : $app->api()->foil($which);
    }
}

if (! function_exists('Foil\engine')) {
    /**
     * This function is the preferred way to be used to create a Foil engine.
     *
     * @param  array        $options   Options: autoescape, default and allowed extensions, folders...
     * @param  array        $providers Custom service provider classes
     * @return \Foil\Engine
     */
    function engine(array $options = [], array $providers = [])
    {
        return foil('engine', $options, $providers);
    }
}

if (! function_exists('Foil\render_template')) {
    /**
     * @param  string $path    Full path for the template
     * @param  array  $data    Template context
     * @param  array  $options Options for the engine
     * @return string
     */
    function render_template($path, array $data = [], array $options = [])
    {
        foil('engine', $options);

        return foil()->renderTemplate($path, $data);
    }
}

if (! function_exists('Foil\option')) {
    /**
     * Return options array or optionally a specific option whose name is passed in $which param
     *
     * @param  string                   $which
     * @return mixed
     * @throws InvalidArgumentException When $which param isn't a string nor a valid option name
     * @deprecated
     */
    function option($which = null)
    {
        return foil()->option($which);
    }
}

if (! function_exists('Foil\add_context')) {
    /**
     * Add some data for specific templates based on a search or on a regex match.
     *
     * @param array   $data   Data to set for the templates
     * @param string  $needle String to compare template name to
     * @param boolean $regex  If true template name will be compared using $needle as a regex
     * @deprecated
     */
    function add_context(array $data, $needle, $regex = false)
    {
        foil()->addContext($data, $needle, $regex);
    }
}

if (! function_exists('Foil\add_global_context')) {
    /**
     * Add data to all templates
     *
     * @param array $data
     * @deprecated
     */
    function add_global_context(array $data)
    {
        foil()->addGlobalContext($data);
    }
}

if (! function_exists('Foil\add_context_using')) {
    /**
     * Add a custom context class
     *
     * @param ContextInterface $context
     * @deprecated
     */
    function add_context_using(ContextInterface $context)
    {
        foil()->addContextUsing($context);
    }
}

if (! function_exists('Foil\run')) {
    /**
     * Run a registered custom function
     *
     * @param  string $function Function name
     * @return mixed
     * @deprecated
     */
    function run($function)
    {
        return call_user_func_array([foil(), __FUNCTION__], func_get_args());
    }
}

if (! function_exists('Foil\fire')) {
    /**
     * Fire an event using Foil event emitter
     *
     * @param string $event
     * @deprecated
     */
    function fire($event)
    {
        call_user_func_array([foil(), __FUNCTION__], func_get_args());
    }
}

if (! function_exists('Foil\on')) {
    /**
     * Listen to an event using Foil event emitter
     *
     * @param string   $event
     * @param callable $callback
     * @param bool     $once
     * @deprecated
     */
    function on($event, callable $callback, $once = false)
    {
        foil()->on($event, $callback, $once);
    }
}

if (! function_exists('Foil\entities')) {
    /**
     * @param  mixed  $data
     * @param  string $strategy
     * @param  string $encoding
     * @return mixed
     * @deprecated
     */
    function entities($data, $strategy = 'html', $encoding = null)
    {
        return foil()->entities($data, $strategy, $encoding);
    }
}

if (! function_exists('Foil\decode')) {
    /**
     * @param  mixed  $data
     * @param  string $encoding
     * @return mixed
     * @deprecated
     */
    function decode($data, $encoding = null)
    {
        return foil()->decode($data, $encoding);
    }
}

if (! function_exists('Foil\arraize')) {
    /**
     * @param  array $data
     * @param  bool  $escape
     * @param  array $transformers
     * @param  bool  $toString
     * @return mixed
     */
    function arraize($data = [], $escape = false, array $transformers = [], $toString = false)
    {
        $flags = $escape ? Kernel\Arraize::ESCAPE : 0;
        $flags |= $toString ? Kernel\Arraize::TOSTRING : 0;

        return call_user_func(new Kernel\Arraize($data, $transformers, $flags));
    }
}
