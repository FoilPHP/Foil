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

use Foil\Contracts\EngineInterface;
use Foil\Contracts\TemplateAwareInterface as TemplateAware;
use Foil\Contracts\FinderAwareInterface as FinderAware;
use Foil\Contracts\APIAwareInterface as APIAware;
use Foil\Contracts\ExtensionInterface as Extension;
use Foil\Template\Stack;
use Foil\Template\Finder;
use RuntimeException;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Engine implements EngineInterface, TemplateAware, FinderAware, APIAware
{
    use Traits\TemplateAwareTrait;
    use Traits\FinderAwareTrait;
    use Traits\APIAwareTrait;

    private $status;
    private static $safe_functions = ['useData', 'useContext'];

    public function __construct(Stack $stack, Finder $finder, API $api)
    {
        $this->setStack($stack);
        $this->setFinder($finder);
        $this->setAPI($api);
        $this->status = self::STATUS_IDLE;
    }

    /**
     * @param  string       $name
     * @param  array        $arguments
     * @return \Foil\Engine Itself for fluent interface
     */
    public function __call($name, array $arguments)
    {
        if (! in_array($name, self::$safe_functions, true)) {
            throw new LogicException($name.' is not a valid engine method.');
        }
        $this->api()->fire('f.engine.call', $name, $arguments);

        return $this;
    }

    /**
     * Return current engine status
     *
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Register an extension
     *
     * @param  Extension    $extension
     * @param  array        $options   Extension options
     * @param  mixed        $safe      Array of safe functions names, true or false for all / none
     * @return \Foil\Engine Itself for fluent interface
     */
    public function loadExtension(Extension $extension, array $options = [], $safe = false)
    {
        $this->api()->fire('f.extension.load', $extension, $options, $safe);

        return $this;
    }

    /**
     * Register a single filter
     *
     * @param  string       $filter_name
     * @param  callable     $filter
     * @return \Foil\Engine Itself for fluent interface
     */
    public function registerFilter($filter_name, callable $filter)
    {
        $this->api()->fire('f.filter.register', $filter_name, $filter);

        return $this;
    }

    /**
     * Register a single function
     *
     * @param  string       $function_name
     * @param  callable     $function
     * @param  boolean      $safe          Can function output html?
     * @return \Foil\Engine Itself for fluent interface
     */
    public function registerFunction($function_name, callable $function, $safe = false)
    {
        $this->api()->fire('f.function.register', $function_name, $function, $safe);

        return $this;
    }

    /**
     * Set folders where to search for templates
     *
     * @param  array        $folders
     * @return \Foil\Engine
     */
    public function setFolders(array $folders)
    {
        $this->finder()->in($folders);

        return $this;
    }

    /**
     * Add a template folder. Is possible to give a name to the folder to be able to specifically
     * call templates from there.
     *
     * @param  string       $path
     * @param  string|void  $name
     * @return \Foil\Engine Itself for fluent interface
     */
    public function addFolder($path, $name = null)
    {
        $this->finder()->in([$name => $path]);

        return $this;
    }

    /**
     * Find a template in registered folders.
     *
     * @param  string         $template
     * @return string|boolean Full path of template if found, false otherwise
     */
    public function find($template)
    {
        return $this->finder()->find($template);
    }

    /**
     * Render a given template with given data.
     *
     * @param  string           $template
     * @param  array            $data
     * @return string
     * @throws RuntimeException
     */
    public function render($template, array $data = [])
    {
        $path = $this->find($template);
        if ($path) {
            return $this->doRender($path, $data);
        }
        throw new RuntimeException($template.' is not a valid template name.');
    }

    /**
     * Render a template file given its full path and an array of data.
     *
     * @param  string $path
     * @param  array  $data
     * @return string
     */
    public function renderTemplate($path, array $data = [])
    {
        if (file_exists($path)) {
            return $this->doRender($path, $data);
        }
        throw new RuntimeException(__METHOD__.' needs a valid template path as first argument.');
    }

    /**
     * Render a template file its name and an array of data.
     *
     * @param  string $path
     * @param  array  $data
     * @return string
     */
    private function doRender($path, array $data = [])
    {
        if ($this->status() === self::STATUS_IDLE) {
            $this->statusTransitions();
        }
        $template = $this->stack()->factory($path);
        $this->api()->fire('f.template.render', $template, $data);
        $output = trim($template->render($data));
        $this->api()->fire('f.template.renderered', $template, $output);

        return $output;
    }

    /**
     * Used internally to keep engine status updated.
     * Using events is possible to keep in sync template flow with engine status without giving
     * to templates write access to Engine status.
     *
     * @access private
     */
    private function statusTransitions()
    {
        $this->status = self::STATUS_IN_LAYOUT;
        $this->api()->on('f.template.layout', function () {
            if ($this->status & self::STATUS_IN_PARTIAL) {
                throw new LogicException('Is not possible to use $this->layout() in partials.');
            }
            $this->status = self::STATUS_IN_TEMPLATE;
        });
        $this->api()->on('f.template.renderlayout', function () {
            $this->status = self::STATUS_IN_LAYOUT;
        });
        $this->api()->on('f.template.prepartial', function () {
            $this->status |= self::STATUS_IN_PARTIAL;
        });
        $this->api()->on('f.template.afterpartial', function () {
            $this->status ^= self::STATUS_IN_PARTIAL;
        });
        $this->api()->on('f.template.renderered', function () {
            $this->stack()->pop();
            if ($this->stack()->count() === 0) {
                $this->status = self::STATUS_RENDERED;
            }
        });
    }
}
