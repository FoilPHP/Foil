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
 * Core object of the package, is the front controller for all templates features.
 * Function in template files are executed in the context of this object:
 * in template files `$this` refers to a `TemplateInterface` object.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface TemplateInterface
{
    /**
     * Render a template with given data
     *
     * @param  array  $data
     * @return string
     */
    public function render(array $data = []);

    /**
     * Render content from another template (partial) in current one.
     *
     * @param  string     $template Partial template name
     * @param  array      $data     Partial-specific data
     * @param  array|void $only     Variable names partial have to inherit from current context
     * @return string
     */
    public function insert($template, array $data = [], array $only = null);

    /**
     * Set a layout to be used for template
     *
     * @param string     $layout Layout name
     * @param array      $data   Layout-specific data
     * @param array|void $only   Variable names layout have to inherit from current context
     */
    public function layout($layout, array $data = [], array $only = null);

    /**
     * Returns the content for a given section, or a default text if section is not present
     *
     * @param  string          $section Section identifier
     * @param  string|callable $default Default content
     * @return string
     */
    public function supply($section, $default = '');

    /**
     * Returns full path for template file
     *
     * @return string
     */
    public function path();

    /**
     * Return data for the template
     *
     * @return string
     */
    public function data();

    /**
     * Set data for the template
     *
     * @param  array  $data
     * @return string
     */
    public function setData(array $data);

    /**
     * Run a registered function. Methods may receive any number of argument.
     *
     * @param  string $function
     * @return mixed
     */
    public function run($function);

    /**
     * Filter an input variable with a given filter.
     *
     * @param  string $filter
     * @param  string $input
     * @return mixed
     */
    public function filter($filter, $input);

    /**
     * In a layout return content from extending template that was not included in any section.
     *
     * @return string
     */
    public function buffer();
}
