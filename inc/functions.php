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
        return Foil::boot($options, $providers)->engine();
    }
}

if (! function_exists('Foil\render')) {
    /**
     * @param  string $path      Full path or just name (requires folders option) for the template
     * @param  array  $data      Template context
     * @param  array  $options   Options for the engine
     * @param  array  $providers
     * @return string
     */
    function render($path, array $data = [], array $options = [], array $providers = [])
    {
        $foil = Foil::boot($options, $providers);

        return $foil->engine()->render($path, $data);
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
