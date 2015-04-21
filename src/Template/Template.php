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

use Foil\Contracts\TemplateInterface;
use Foil\API;
use Foil\Contracts\APIAwareInterface as APIAware;
use ArrayAccess;
use Foil\Traits;
use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Template implements TemplateInterface, APIAware
{
    use Traits\APIAwareTrait;
    use Traits\DataHandlerTrait;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \ArrayAccess
     */
    private $sections;

    /**
     * @var string
     */
    private $layout;

    /**
     * @var array
     */
    private $layout_data = [];

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var string
     */
    private $last_buffer = '';

    /**
     * @param string       $path
     * @param \ArrayAccess $sections
     * @param \Foil\API    $api
     */
    public function __construct($path, ArrayAccess $sections, API $api)
    {
        $this->path = $path;
        $this->sections = $sections;
        $this->setAPI($api);
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
            $input = $this->api()->foil('command')->filter($filter, $input, $args[$i]);
        }, $args);

        return $input;
    }

    /**
     * @inheritdoc
     */
    public function run($function)
    {
        return call_user_func_array([$this->api()->foil('command'), 'run'], func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function supply($section, $default = '')
    {
        if ($this->sections->offsetExists($section)) {
            return $this->sections[$section]->content();
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
        $this->api()->fire('f.template.prepartial', $template, $data, $this);
        $partial = $this->api()->engine()->render($template, $this->buildContext($data, $only));
        $this->api()->fire('f.template.afterpartial', $this);

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
        return $this->api()->engine()->find($template)
            ? $this->insert($template, $data, $only)
            : '';
    }

    /**
     * @inheritdoc
     */
    public function layout($layout, array $data = [], array $only = null)
    {
        $layout_file = file_exists($layout) ? $layout : $this->api()->engine()->find($layout);
        if (! $layout_file) {
            throw new InvalidArgumentException('Layout must be a valid file name.');
        }
        $this->layout = $layout_file;
        $this->layout_data[$layout_file] = ['data' => $data, 'only' => $only];
        // listener for this event makes sections work in non-output mode
        $this->api()->fire('f.template.layout', $layout_file, $this);

        return $this->layout;
    }

    /**
     * @inheritdoc
     */
    public function render(array $data = [])
    {
        $this->api()->fire('f.template.prerender', $this);
        $this->setData(array_merge($this->data(), $data));
        $output = $this->collect($this->path());
        while ($this->layoutPath()) {
            $layout = $this->layoutPath();
            $this->setData($this->buildContext(
                $this->layout_data[$layout]['data'],
                $this->layout_data[$layout]['only']
            ));
            $this->layout = null;
            // listener for this event makes sections work in output mode
            $this->api()->fire('f.template.renderlayout', $layout, $this);
            $output = $this->collect($layout);
        }

        $this->api()->fire('f.template.rendered', $this);

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
     * Return las buffer.
     */
    public function lastBuffer()
    {
        return $this->last_buffer;
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
    private function collect($path)
    {
        ob_start();
        require $path;
        $this->last_buffer = $this->buffer;
        $this->buffer = trim(ob_get_clean());

        return $this->buffer;
    }

    /**
     * @param  array $data
     * @param  array $only
     * @return array
     */
    private function buildContext(array $data = [], array $only = null)
    {
        $now = is_null($only)
            ? $this->data()
            : array_intersect_key($this->data(), array_flip($only));

        return array_merge($now, $data);
    }
}
