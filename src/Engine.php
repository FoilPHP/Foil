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
use Foil\Contracts\ExtensionInterface as Extension;
use Foil\Kernel\Events;
use Foil\Template\Stack;
use Foil\Template\Finder;
use RuntimeException;
use LogicException;
use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @method Engine useData()
 * @method Engine useContext()
 */
class Engine implements EngineInterface, TemplateAware, FinderAware
{
    use Traits\TemplateAwareTrait;
    use Traits\FinderAwareTrait;
    /**
     * @var array
     */
    private static $safeFunctions = ['useData', 'useContext'];

    /**
     * @var int
     */
    private $status;

    /**
     * @var \Foil\Kernel\Events
     */
    private $events;

    /**
     * @param \Foil\Template\Stack  $stack
     * @param \Foil\Template\Finder $finder
     * @param \Foil\Kernel\Events   $events
     */
    public function __construct(Stack $stack, Finder $finder, Events $events)
    {
        $this->setStack($stack);
        $this->setFinder($finder);
        $this->events = $events;
        $this->status = self::STATUS_IDLE;
    }

    /**
     * @param  string       $name
     * @param  array        $arguments
     * @return \Foil\Engine Itself for fluent interface
     */
    public function __call($name, array $arguments)
    {
        if (! in_array($name, self::$safeFunctions, true)) {
            throw new LogicException($name.' is not a valid engine method.');
        }
        $this->fire('f.engine.call', $name, $arguments);

        return $this;
    }

    public function fire()
    {
        call_user_func_array([$this->events, 'fire'], func_get_args());
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
        $this->fire('f.extension.load', $extension, $options, $safe);

        return $this;
    }

    /**
     * Register a single filter
     *
     * @param  string       $filterName
     * @param  callable     $filter
     * @return \Foil\Engine Itself for fluent interface
     */
    public function registerFilter($filterName, callable $filter)
    {
        $this->fire('f.filter.register', $filterName, $filter);

        return $this;
    }

    /**
     * Register a single function
     *
     * @param  string       $functionName
     * @param  callable     $function
     * @param  boolean      $safe         Can function output html?
     * @return \Foil\Engine Itself for fluent interface
     */
    public function registerFunction($functionName, callable $function, $safe = false)
    {
        $this->fire('f.function.register', $functionName, $function, $safe);

        return $this;
    }

    /**
     * Register a block.
     *
     * @param  string       $name
     * @param  callable     $callback
     * @return \Foil\Engine Itself for fluent interface
     */
    public function registerBlock($name, callable $callback)
    {
        $this->fire('f.block.register', $name, $callback);

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
        $this->finder()->in($folders, true);

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
        $folders = is_string($name) ? [$name => $path] : [$path];
        $this->finder()->in($folders);

        return $this;
    }

    /**
     * Find a template in registered folders.
     *
     * @param  string|array   $template
     * @return string|boolean Full path of template if found, false otherwise
     */
    public function find($template)
    {
        return $this->finder()->find($template);
    }

    /**
     * Render a given template with given data.
     *
     * @param  string|array     $template
     * @param  array            $data
     * @return string
     * @throws RuntimeException
     */
    public function render($template, array $data = [])
    {
        $isString = is_string($template);

        if ($isString && is_file($template)) {
            return $this->renderTemplate($template, $data);
        }
        if (! $isString && ! is_array($template)) {
            throw new InvalidArgumentException('Template must be a string or an array of strings.');
        }
        $path = $this->find($template);
        if ($path) {
            return $this->doRender($path, $data);
        }
        if ($isString) {
            throw new RuntimeException($template.' is not a valid template name.');
        }

        throw new RuntimeException('Not found any valid template in the array provided.');
    }

    /**
     * Render a template file given its full path and an array of data.
     *
     * @param  string $path
     * @param  array  $data
     * @param  string $class
     * @return string
     */
    public function renderTemplate($path, array $data = [], $class = null)
    {
        if (file_exists($path)) {
            return $this->doRender($path, $data, $class);
        }
        throw new RuntimeException(__METHOD__.' needs a valid template path as first argument.');
    }

    /**
     * Renders a template and returns the rendered content of a specific passed section.
     * Section param can also be an array of section names in which case the method will return
     * an array where keys are required section names, values the related rendered content.
     *
     * @param  string       $template
     * @param  string|array $section
     * @param  array        $data
     * @param  string|null  $class
     * @return string
     */
    public function renderSection($template, $section, array $data = [], $class = null)
    {
        if (! is_array($section) && ! is_string($section)) {
            throw new InvalidArgumentException(
                'Section to render must be passed as string or array of strings.'
            );
        }
        $sections = is_string($section) ? [$section] : array_filter($section, 'is_string');
        $outputs = new \stdClass();
        $setter = function ($name, $content) use ($sections, $outputs) {
            in_array($name, $sections, true) and $outputs->$name = $content;
        };
        $this->events->on('f.sections.content', $setter);
        $this->render($template, $data, $class);
        $this->events->removeListener('f.sections.content', $setter);
        $result = array_merge(array_fill_keys($sections, ''), get_object_vars($outputs));

        return is_array($section) ? $result : $result[$section];
    }

    /**
     * Render a template and return an array where the keys are template section names and values
     * the section rendered content.
     *
     * @param  string $template
     * @param  array  $data
     * @param  null   $class
     * @return array
     */
    public function renderSections($template, array $data = [], $class = null)
    {
        $outputs = new \stdClass();
        $setter = function ($name, $content) use ($outputs) {
            $outputs->$name = $content;
        };
        $this->events->on('f.sections.content', $setter);
        $this->render($template, $data, $class);
        $this->events->removeListener('f.sections.content', $setter);

        return get_object_vars($outputs);
    }

    /**
     * Render a template file its name and an array of data.
     *
     * @param  string $path
     * @param  array  $data
     * @param  null   $class
     * @return string
     */
    private function doRender($path, array $data = [], $class = null)
    {
        $status = $this->status();
        if ($status === self::STATUS_IDLE) {
            $this->statusTransitions();
        } elseif ($status & self::STATUS_IDLE) {
            $this->status = self::STATUS_IN_LAYOUT;
        }
        $template = $this->stack()->factory($path, $this, $class);
        $this->events->fire('f.template.render', $template, $data);
        $output = trim($template->render($data));
        $this->events->fire('f.template.renderered', $template, $output, $this->status);

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
        $this->events->on('f.template.layout', function () {
            if ($this->status & self::STATUS_IN_PARTIAL) {
                throw new LogicException('Is not possible to use $this->layout() in partials.');
            }
            $this->status = self::STATUS_IN_TEMPLATE;
        });
        $this->events->on('f.template.renderlayout', function () {
            $this->status = self::STATUS_IN_LAYOUT;
        });
        $this->events->on('f.template.prepartial', function () {
            $this->status |= self::STATUS_IN_PARTIAL;
        });
        $this->events->on('f.template.afterpartial', function () {
            $this->status ^= self::STATUS_IN_PARTIAL;
        });
        $this->events->on('f.template.renderered', function () {
            $this->stack()->pop();
            if ($this->stack()->count() === 0) {
                $this->status = self::STATUS_RENDERED | self::STATUS_IDLE;
                $this->events->fire('f.renderered', $this);
            }
        });
    }
}
