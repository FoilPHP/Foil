<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Contracts;

/**
 * Engine object is the object to be mainly used to interact with client code.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface EngineInterface
{
    const STATUS_IDLE        = 1;
    const STATUS_IN_LAYOUT   = 2;
    const STATUS_IN_TEMPLATE = 4;
    const STATUS_RENDERED    = 8;
    const STATUS_IN_PARTIAL  = 16;

    /**
     * Find a template full path for a given template name.
     * Templates will be searched in all registered folders.
     * If extension is not given default one is added.
     * File with matching names but not accepted exceptions will be skipped.
     *
     * @param string $template
     */
    public function find($template);

    /**
     * Render a template with given data
     *
     * @param  string $template
     * @param  array  $data
     * @return string
     */
    public function render($template, array $data = []);

    /**
     * Register a function to be used in templates
     *
     * @param string   $functionName
     * @param callable $function
     */
    public function registerFunction($functionName, callable $function);

    /**
     * Register a filter to be used in templates
     *
     * @param  string          $filterName
     * @param  callable        $filter
     * @return EngineInterface Itself, for fluent interface
     */
    public function registerFilter($filterName, callable $filter);

    /**
     * Load an extension into the engine
     *
     * @param  ExtensionInterface $extension
     * @return EngineInterface    Itself, for fluent interface
     */
    public function loadExtension(ExtensionInterface $extension);

    /**
     * Add a folder to be used for templates
     *
     * @param  string          $path
     * @param  string          $name
     * @return EngineInterface Itself, for fluent interface
     */
    public function addFolder($path, $name = null);

    /**
     * Set folders to be used for templates
     *
     * @param  array           $folders
     * @return EngineInterface Itself, for fluent interface
     */
    public function setFolders(array $folders);

    /**
     * Get engine status
     *
     * @return int One of the statues constants
     */
    public function status();
}
