<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil;

use Foil\Contracts\ContextInterface;
use Pimple\Container;
use Foil\Context\RegexContext;
use Foil\Context\SearchContext;
use Foil\Context\GlobalContext;
use Foil\Kernel\Arraize;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * YOLO
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class API
{
    /**
     * @param \Pimple\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Here for backward compatibility to allow calling methods using snake_case names.
     * Will be removed in future versions.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        $method = str_replace(' ', '', lcfirst(ucwords(str_replace('_', ' ', $name))));
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        }
        throw new BadMethodCallException("{$name} is not a valid Foil API method.");
    }

    /**
     * @param  string $which
     * @return mixed
     */
    public function get($which)
    {
        return $this->container[$which];
    }

    /**
     * @param  null                    $which
     * @return mixed|\Pimple\Container
     * @deprecated
     */
    public function foil($which = null)
    {
        return is_null($which) ? $this->container : $this->container[$which];
    }

    /**
     * @param  array $options
     * @param  array $providers
     * @return mixed
     * @deprecated
     */
    public function engine(array $options = null, array $providers = null)
    {
        $container = is_null($options)
            ? $this->container
            : Foil::boot($options, $providers);

        return $container['engine'];
    }

    /**
     * Render a template using a full template file path and some data.
     * When used before any engine() call, is possible to set engine options.
     *
     * @param  string $path
     * @param  array  $data
     * @param  array  $options
     * @return string
     */
    public function renderTemplate($path, array $data = [], array $options = null)
    {
        $container = is_null($options) ? $this->container : Foil::boot($options);
        /** @var \Foil\Engine $engine */
        $engine = $container['engine'];

        return $engine->renderTemplate($path, $data);
    }

    /**
     * Return options array or optionally a specific option whose name is passed in $which param.
     *
     * @param  null  $which
     * @return mixed
     */
    public function option($which = null)
    {
        if (! is_null($which) && ! is_string($which)) {
            throw new InvalidArgumentException('Option name must be in a string.');
        }
        $options = $this->container['options'];

        return is_null($which) ? $options : $options[$which];
    }

    /**
     * Add some data for specific templates based on a search or on a regex match.
     *
     * @param array  $data
     * @param string $needle
     * @param bool   $regex
     */
    public function addContext(array $data, $needle, $regex = false)
    {
        $context = empty($regex)
            ? new SearchContext($needle, $data)
            : new RegexContext($needle, $data);
        $this->container['context']->add($context);
    }

    /**
     * Add data to all templates.
     *
     * @param array $data
     */
    public function addGlobalContext(array $data)
    {
        $this->container['context']->add(new GlobalContext($data));
    }

    /**
     * Add a custom context class.
     *
     * @param \Foil\Contracts\ContextInterface $context
     */
    public function addContextUsing(ContextInterface $context)
    {
        $this->container['context']->add($context);
    }

    /**
     * Run a registered custom function.
     *
     * @param  string $function
     * @return mixed
     */
    public function run($function)
    {
        if (! is_string($function)) {
            throw new InvalidArgumentException('Function name must be in a string.');
        }

        return call_user_func_array([$this->container['command'], 'run'], func_get_args());
    }

    /**
     * Fire an event using Foil event emitter.
     *
     * @param string $event
     */
    public function fire($event)
    {
        if (! is_string($event)) {
            throw new InvalidArgumentException('Event name must be in a string.');
        }
        call_user_func_array([$this->container['events'], 'fire'], func_get_args());
    }

    /**
     * Listen to an event using Foil event emitter.
     *
     * @param string   $event
     * @param callable $callback
     * @param bool     $once
     */
    public function on($event, callable $callback, $once = false)
    {
        if (! is_string($event)) {
            throw new InvalidArgumentException('Event name must be in a string.');
        }
        $method = empty($once) ? 'on' : 'once';
        $this->container['events']->$method($event, $callback);
    }

    /**
     * Escape strings and array using AuraPHP HTML library.
     *
     * @param  mixed       $data
     * @param  string      $strategy
     * @param  string|null $encoding
     * @return mixed
     */
    public function entities($data, $strategy = 'html', $encoding = null)
    {
        return $this->container['escaper']->escape($data, $strategy, $encoding);
    }

    /**
     * Decode strings and array from HTML entities.
     *
     * @param  mixed       $data
     * @param  string|null $encoding
     * @return mixed
     */
    public function decode($data, $encoding = null)
    {
        return $this->container['escaper']->decode($data, $encoding);
    }

    /**
     * Stateless function that recursively convert an array or a traversable object into a nested
     * array. Optionally convert all "atomic" items to strings and optionally HTML-encode all
     * strings. Nested array and traversable objects are all converted recursively. Non-traversable
     * objects are converted to array, in 1st available among following 5 methods:
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
    public function arraize(
        $data = [],
        $escape = false,
        array $transformers = [],
        $toString = false
    ) {
        $flags = $escape ? Arraize::ESCAPE : 0;
        $flags |= $toString ? Arraize::TOSTRING : 0;

        return call_user_func(new Arraize($data, $transformers, $flags));
    }
}
