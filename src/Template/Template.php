<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Template;

use Foil\Contracts\AliasAllowedTemplateInterface;
use Foil\Engine;
use Foil\Kernel\Command;
use Foil\Section\Factory as SectionFactory;
use Foil\Traits;
use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Template implements AliasAllowedTemplateInterface
{
    use Traits\DataHandlerTrait;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \Foil\Section\Factory
     */
    private $sections;

    /**
     * @var \Foil\Engine
     */
    private $engine;

    /**
     * @var \Foil\Kernel\Command
     */
    private $command;

    /**
     * @var \Foil\Template\Alias|null
     */
    private $alias;

    /**
     * @var string
     */
    private $layout;

    /**
     * @var array
     */
    private $layoutData = [];

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var string
     */
    private $lastBuffer = '';

    /**
     * @param string                $path
     * @param \Foil\Section\Factory $sections
     * @param \Foil\Engine          $engine
     * @param \Foil\Kernel\Command  $command
     */
    public function __construct(
        $path,
        SectionFactory $sections,
        Engine $engine,
        Command $command
    ) {
        $this->path = $path;
        $this->sections = $sections;
        $this->engine = $engine;
        $this->command = $command;
    }

    /**
     * Proxies non-existent methods to command to call registered extensions functions
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        array_unshift($arguments, $name);

        return call_user_func_array([$this, 'run'], $arguments);
    }

    /**
     * Uses core helper extension function to get a value from template data
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->run('v', $name);
    }

    /**
     * @inheritdoc
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function alias(Alias $alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function filter($filter, $input)
    {
        $filters = is_string($filter) ? explode('|', $filter) : array_values((array) $filter);
        if (func_num_args() > 2 && is_array(func_get_arg(2))) {
            $args = count($filters) === 1
                ? [func_get_arg(2)]
                : array_filter(func_get_arg(2), 'is_array');
        } else {
            $args = array_fill(0, count($filters), []);
        }
        if (count($args) !== count($filters)) {
            throw new InvalidArgumentException(
                'Args array must contain as many sub-arrays as filters number.'
            );
        }
        array_walk($filters, function ($filter, $i, $args) use (&$input) {
            $input = $this->command->filter($filter, $input, $args[$i]);
        }, $args);

        return $input;
    }

    /**
     * @inheritdoc
     */
    public function run($function)
    {
        return call_user_func_array([$this->command, 'run'], func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function supply($section, $default = '')
    {
        if ($this->sections->has($section)) {
            return $this->sections->get($section)->content();
        }
        while (is_callable($default)) {
            $default = $default($section, $this);
        }

        return is_string($default) ? $default : '';
    }

    /**
     * @inheritdoc
     */
    public function insert($template, array $data = [], array $only = null)
    {
        $this->engine->fire('f.template.prepartial', $template, $data, $this);
        $partial = $this->engine->render($template, $this->buildContext($data, $only));
        $this->engine->fire('f.template.afterpartial', $this);

        return $partial;
    }

    /**
     * Insert a partial only if exists.
     *
     * @param  string $template
     * @param  array  $data
     * @param  array  $only
     * @return string
     */
    public function insertif($template, array $data = [], array $only = null)
    {
        return $this->engine->find($template) ? $this->insert($template, $data, $only) : '';
    }

    /**
     * @inheritdoc
     */
    public function layout($layout, array $data = [], array $only = null)
    {
        $file = file_exists($layout) ? $layout : $this->engine->find($layout);
        if (! $file) {
            throw new InvalidArgumentException('Layout must be a valid file name.');
        }
        $this->layout = $file;
        $this->layoutData[$file] = ['data' => $data, 'only' => $only];
        // listener for this event makes sections work in non-output mode
        $this->engine->fire('f.template.layout', $file, $this);

        return $this->layout;
    }

    /**
     * @inheritdoc
     */
    public function render(array $data = [])
    {
        $this->engine->fire('f.template.prerender', $this);
        $this->setData(array_merge($this->data(), $data));
        $output = $this->collect($this->path());
        while ($this->layoutPath()) {
            $layout = $this->layoutPath();
            $this->setData($this->buildContext(
                $this->layoutData[$layout]['data'],
                $this->layoutData[$layout]['only']
            ));
            $this->layout = null;
            // listener for this event makes sections work in output mode
            $this->engine->fire('f.template.renderlayout', $layout, $this);
            $output = $this->collect($layout);
        }

        $this->engine->fire('f.template.rendered', $this);

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function buffer()
    {
        return $this->buffer;
    }

    /**
     * Return last buffer.
     */
    public function lastBuffer()
    {
        return $this->lastBuffer;
    }

    /**
     * Return current layout path if any.
     *
     * @return string|void
     */
    public function layoutPath()
    {
        return $this->layout;
    }

    /**
     * Load a template file, save the collected buffer in $buffer var and return it
     *
     * @param  string $path Template file path
     * @return string
     * @access private
     */
    protected function collect($path)
    {
        ob_start();
        $this->alias and extract(["{$this->alias}" => $this], EXTR_SKIP);
        /** @noinspection PhpIncludeInspection */
        require $path;
        $this->lastBuffer = $this->buffer;
        $this->buffer = trim(ob_get_clean());

        return $this->buffer;
    }

    /**
     * @param  array $data
     * @param  array $only
     * @return array
     */
    protected function buildContext(array $data = [], array $only = null)
    {
        $now = is_null($only)
            ? $this->data()
            : array_intersect_key($this->data(), array_flip($only));

        return array_merge($now, $data);
    }
}
