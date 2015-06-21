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

use Foil\Context\SearchContext;
use Foil\Context\RegexContext;
use Foil\Context\GlobalContext;
use Foil\Contracts\ContextInterface;
use Foil\Kernel\Arraize;
use LogicException;
use InvalidArgumentException;

if (! function_exists('Foil\foil')) {
    /**
     * On first call instantiate the container (Pimple) and register service providers.
     * On subsequent calls returns container or a service whose id has been passed as argument.
     *
     * @staticvar \Pimple\Container     $container
     * @param  string|void              $which            Service id
     * @param  array                    $options          Engine options
     * @param  array                    $custom_providers Custom service provider classes
     * @return mixed                    The container or the service whose id has been passed in $which
     * @throws LogicException           If used to read service before engine has been set
     * @throws InvalidArgumentException If service id is not a string or service is not registered
     */
    function foil($which = null, array $options = [], array $custom_providers = [])
    {
        static $container = null;
        if (is_null($container) && $which !== 'engine') {
            throw new LogicException('Engine must be instantiated before to retrieve any service.');
        } elseif (is_null($container)) {
            $bootstrapper = new Bootstrapper();
            $providers = [
                'kernel'     => '\\Foil\\Providers\\Kernel',
                'aura_html'  => '\\Foil\\Providers\\AuraHtml',
                'core'       => '\\Foil\\Providers\\Core',
                'context'    => '\\Foil\\Providers\\Context',
                'extensions' => '\\Foil\\Providers\\Extensions',
                'blocks'     => '\\Foil\\Providers\\Blocks',
            ];
            if (! empty($custom_providers)) {
                $providers = array_merge($providers, array_filter($custom_providers, 'is_string'));
            }
            $container = $bootstrapper->init($options, array_values($providers));
            $container['api'] = new API();
            $bootstrapper->boot($container);
        } elseif (! is_null($which) && ! is_string($which)) {
            throw new InvalidArgumentException('Service name must be in a string.');
        }

        return is_null($which) ? $container : $container[$which];
    }
}

if (! function_exists('Foil\engine')) {
    /**
     * This function is the preferred way to be used to create a Foil engine.
     *
     * @param  array        $options Options: autoescape, default and allowed extensions, folders...
     * @return \Foil\Engine
     */
    function engine(array $options = [])
    {
        return foil('engine', $options);
    }
}

if (! function_exists('Foil\render_template')) {
    /**
     * Render a template using a full template file path and some data.
     * When used before any engine() call, is possible to set engine options.
     *
     * @param  string $path    Full path for the template
     * @param  array  $data    Template context
     * @param  array  $options Options for the engine
     * @return string
     */
    function render_template($path, array $data = [], array $options = [])
    {
        return engine($options)->renderTemplate($path, $data);
    }
}

if (! function_exists('Foil\option')) {
    /**
     * Return options array or optionally a specific option whose name is passed in $which param
     *
     * @param  string                   $which
     * @return mixed
     * @throws InvalidArgumentException When $which param isn't a string nor a valid option name
     */
    function option($which = null)
    {
        if (! is_null($which) && ! is_string($which)) {
            throw new InvalidArgumentException('Option name must be in a string.');
        }
        $options = foil('options');

        return is_null($which) ? $options : $options[$which];
    }
}

if (! function_exists('Foil\add_context')) {
    /**
     * Add some data for specific templates based on a search or on a regex match.
     *
     * @param array   $data     Data to set for the templates
     * @param string  $needle   String to compare template name to
     * @param boolean $is_regex If true template name will be compared using $needle as a regex
     */
    function add_context(array $data, $needle, $is_regex = false)
    {
        $context = empty($is_regex)
            ? new SearchContext($needle, $data)
            : new RegexContext($needle, $data);
        foil('context')->add($context);
    }
}

if (! function_exists('Foil\add_global_context')) {
    /**
     * Add data to all templates
     *
     * @param array $data
     */
    function add_global_context(array $data)
    {
        foil('context')->add(new GlobalContext($data));
    }
}

if (! function_exists('Foil\add_context_using')) {
    /**
     * Add a custom context class
     *
     * @param ContextInterface $context
     */
    function add_context_using(ContextInterface $context)
    {
        foil('context')->add($context);
    }
}

if (! function_exists('Foil\run')) {
    /**
     * Run a registered custom function
     *
     * @param  string $function Function name
     * @return mixed
     */
    function run($function)
    {
        if (! is_string($function)) {
            throw new InvalidArgumentException('Function name must be in a string.');
        }

        return call_user_func_array([foil('command'), 'run'], func_get_args());
    }
}

if (! function_exists('Foil\fire')) {
    /**
     * Fire an event using Foil event emitter
     *
     * @param string $event
     */
    function fire($event)
    {
        if (! is_string($event)) {
            throw new InvalidArgumentException('Event name must be in a string.');
        }
        call_user_func_array([foil('events'), 'fire'], func_get_args());
    }
}

if (! function_exists('Foil\on')) {
    /**
     * Listen to an event using Foil event emitter
     *
     * @param string   $event
     * @param callable $callback
     * @param bool     $once
     */
    function on($event, callable $callback, $once = false)
    {
        if (! is_string($event)) {
            throw new InvalidArgumentException('Event name must be in a string.');
        }
        $cb = empty($once) ? 'on' : 'once';
        foil('events')->$cb($event, $callback);
    }
}

if (! function_exists('Foil\entities')) {
    /**
     * Escape strings and array using AuraPHP HTML library.
     *
     * @param  mixed  $data
     * @param  string $strategy
     * @param  string $encoding
     * @return mixed
     */
    function entities($data, $strategy = 'html', $encoding = null)
    {
        return foil('escaper')->escape($data, $strategy, $encoding);
    }
}

if (! function_exists('Foil\decode')) {
    /**
     * Decode strings and array from HTML entities
     *
     * @param  mixed  $data
     * @param  string $encoding
     * @return mixed
     */
    function decode($data, $encoding = null)
    {
        return foil('escaper')->decode($data, $encoding);
    }
}

if (! function_exists('Foil\arraize')) {
    /**
     * Stateless class that recursively convert an array or a traversable object into a nested array.
     * Optionally convert all "atomic" items to strings and optionally HTML-encode all strings.
     * Nested array and traversable objects are all converted recursively.
     * Non-traversable objects are converted to array, in 1st available among following 5 methods:
     *  - if a transformer class is provided, than transformer transform() method is called
     *  - if the object has a method toArray() it is called
     *  - if the object has a method asArray() it is called
     *  - if the object is an instance of JsonSerializable it is JSON-encoded then decoded
     *  - calling get_object_vars()
     *
     * @param  mixed $data         Data to convert
     * @param  bool  $escape       Should strings in data be HTML-encoded?
     * @param  array $transformers Transformers: full qualified class names, objects or callables
     * @param  bool  $toString     Should all scalar items in data be casted to strings?
     * @return array
     */
    function arraize($data = [], $escape = false, array $transformers = [], $toString = false)
    {
        $flags = $escape ? Arraize::ESCAPE : 0;
        $flags |= $toString ? Arraize::TOSTRING : 0;

        return call_user_func(new Arraize($data, $transformers, $flags));
    }
}
