<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\TemplateAwareInterface as TemplateAware;
use Foil\Kernel\Escaper;
use Foil\Traits;
use igorw;
use Closure;
use RuntimeException;

/**
 * Extension that provides very short functions names to be used in template files to run common
 * tasks, mainly get, escape and filter variables.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Helpers implements ExtensionInterface, TemplateAware
{
    use Traits\TemplateAwareTrait;

    /**
     * @var \Foil\Kernel\Escaper
     */
    private $escaper;

    /**
     * @var bool
     */
    private $autoescape;

    /**
     * @var bool|string
     */
    private $strict;

    /**
     * @param \Foil\Kernel\Escaper $escaper
     * @param array                $options
     */
    public function __construct(Escaper $escaper, array $options)
    {
        $this->escaper = $escaper;
        $this->autoescape = ! isset($options['autoescape']) || ! empty($options['autoescape']);
        if (isset($options['strict_variables'])) {
            $this->strict = strtolower((string) $options['strict_variables']) === 'notice'
                ? 'notice'
                : ! empty($options['strict_variables']);
        }
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function setup(array $args = [])
    {
        return $args;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function provideFilters()
    {
        return [
            'e'      => [$this, 'entities'],
            'escape' => [$this, 'entities']
        ];
    }

    /**
     * @inheritdoc
     */
    public function provideFunctions()
    {
        return [
            'v'      => [$this, 'variable'],
            'e'      => [$this, 'escape'],
            'eJs'    => [$this, 'escapeJs'],
            'eCss'   => [$this, 'escapeCss'],
            'eAttr'  => [$this, 'escapeAttr'],
            'escape' => [$this, 'entities'],
            'ee'     => [$this, 'entities'],
            'd'      => [$this, 'decode'],
            'decode' => [$this, 'decodeEntities'],
            'dd'     => [$this, 'decodeEntities'],
            'in'     => [$this, 'getIn'],
            'raw'    => [$this, 'raw'],
            'a'      => [$this, 'asArray'],
            'araw'   => [$this, 'asArrayRaw'],
            'f'      => [$this, 'filter'],
            'ifnot'  => [$this, 'ifNot'],
        ];
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * If autoescape is set to true strings are escaped for html entities
     *
     * @param  string       $var     Variable name
     * @param  mixed        $default Default
     * @param  string|array $filter  Array or pipe-separated list of filters
     * @return mixed
     */
    public function variable($var, $default = '', $filter = null)
    {
        return $this->autoescape
            ? $this->escape($var, $default, $filter)
            : $this->raw($var, $default, $filter);
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * Strings are escaped using AuraPHP Web library that supports 4 "strategies":
     * 'html', 'js', 'attr' and 'css'.
     *
     * @param  string       $var      Variable name
     * @param  mixed        $default  Default
     * @param  string|array $filter   Array or pipe-separated list of filters
     * @param  string       $strategy Escape strategy, one of 'html', 'js', 'attr', 'css'
     * @param  string|null  $encoding
     * @return mixed
     */
    public function escape(
        $var,
        $default = '',
        $filter = null,
        $strategy = 'html',
        $encoding = null
    ) {
        return $this->escaper->escape(
            $this->raw($var, $default, $filter),
            $strategy,
            $encoding
        );
    }

    /**
     * @param  mixed       $var
     * @param  string      $strategy
     * @param  string|null $encoding
     * @return mixed
     */
    public function entities($var, $strategy = 'html', $encoding = null)
    {
        return $this->escaper->escape($var, $strategy, $encoding);
    }

    /**
     * @param  mixed       $var
     * @param  string|null $encoding
     * @return mixed
     */
    public function decodeEntities($var, $encoding = null)
    {
        return $this->escaper->decode($var, $encoding);
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * Strings are escaped for javascript using AuraPHP Web library.
     *
     * @param  string       $var      Variable name
     * @param  mixed        $default  Default
     * @param  string|array $filter   Array or pipe-separated list of filters
     * @param  string|null  $encoding
     * @return mixed
     */
    public function escapeJs($var, $default = '', $filter = null, $encoding = null)
    {
        return $this->escape($var, $default, $filter, 'js', $encoding);
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * Strings are escaped to be safely used inside CSS code using AuraPHP Web library.
     *
     * @param  string       $var      Variable name
     * @param  mixed        $default  Default
     * @param  string|array $filter   Array or pipe-separated list of filters
     * @param  string|null  $encoding
     * @return mixed
     */
    public function escapeCss($var, $default = '', $filter = null, $encoding = null)
    {
        return $this->escape($var, $default, $filter, 'css', $encoding);
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * Strings are escaped to be safely used inside HTML attributes using AuraPHP Web library.
     *
     * @param  string       $var      Variable name
     * @param  mixed        $default  Default
     * @param  string|array $filter   Array or pipe-separated list of filters
     * @param  string|null  $encoding
     * @return mixed
     */
    public function escapeAttr($var, $default = '', $filter = null, $encoding = null)
    {
        return $this->escape($var, $default, $filter, 'attr', $encoding);
    }

    /**
     * Get and value from template context, optionally set a default and filter.
     * Strings are decoded from html entities.
     *
     * @param  string       $var     Variable name
     * @param  mixed        $default Default
     * @param  string|array $filter  Array or pipe-separated list of filters
     * @return mixed
     */
    public function decode($var, $default = '', $filter = null)
    {
        return $this->escaper->decode($this->raw($var, $default, $filter));
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     *
     * @param  string       $var     Variable name
     * @param  mixed        $default Default
     * @param  string|array $filter  Array or pipe-separated list of filters
     * @return mixed
     */
    public function raw($var, $default = '', $filter = null)
    {
        $data = $this->get($var);
        if (is_null($data['data'])) {
            $data['data'] = $this->returnDefault($default);
        }
        if (is_string($filter)) {
            $filter = explode('|', $filter);
        }
        $filters = array_merge($data['filters'], (array) $filter);
        if (empty($filters)) {
            return $data['data'];
        }

        return $this->template()->filter($filters, $data['data']);
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * If autoescape is set to true strings are escaped for html entities.
     * result is casted to array.
     *
     * @param  string       $var      Variable name
     * @param  mixed        $default  Default
     * @param  string|array $filter   Array or pipe-separated list of filters
     * @param  boolean      $forceRaw Should use raw variable?
     * @return mixed
     */
    public function asArray($var, $default = [], $filter = null, $forceRaw = false)
    {
        $raw = $this->raw($var, $default, $filter);

        return \Foil\arraize($raw, ($this->autoescape && ! $forceRaw));
    }

    /**
     * Return a value from template context, optionally set a default and filter.
     * If autoescape is set to true strings are escaped for html entities.
     * result is casted to array.
     *
     * @param  string       $var     Variable name
     * @param  mixed        $default Default
     * @param  string|array $filter  Array or pipe-separated list of filters
     * @return mixed
     */
    public function asArrayRaw($var, $default = [], $filter = null)
    {
        return $this->asArray($var, $default, $filter, true);
    }

    /**
     * If a value from template context, isn't set or is empty return whatever passed as default.
     *
     * @param  string       $var     Variable name
     * @param  mixed        $default Default
     * @param  string|array $filter  Array or pipe-separated list of filters
     * @return mixed
     */
    public function ifNot($var, $default = '', $filter = null)
    {
        $raw = $this->raw($var, false, $filter);

        return empty($raw) ? $this->returnDefault($default) : '';
    }

    /**
     * Return a value from template context after filter it, optionally set a default.
     * If autoescape is set to true strings are escaped for html entities.
     *
     * @param  string|array $filters Array or pipe-separated list of filters
     * @param  string       $var     Variable name
     * @param  array|void   $args    Array or additional arguments for filters
     * @param  mixed        $default Default
     * @return mixed
     */
    public function filter($filters, $var, array $args = null, $default = '')
    {
        return $this->template()->filter($filters, $this->variable($var, $default), $args);
    }

    /**
     * Allow dot syntax access to any data
     *
     * @param  mixed        $data
     * @param  string|array $where
     * @param  bool         $strict
     * @return mixed
     */
    public function getIn($data, $where, $strict = false)
    {
        if (is_object($data)) {
            $clone = clone $data;
            $data = \Foil\arraize($clone, $this->autoescape);
        } elseif (! is_array($data)) {
            return $this->autoescape ? $this->escaper->escape($data) : $data;
        }
        $where = is_string($where) ? explode('.', $where) : (array) $where;
        $get = igorw\get_in($data, $where);
        if (! $strict || ! $this->strict || ! is_null($get)) {
            return $get;
        }
        $name = implode('.', $where);
        if ($this->strict === 'notice') {
            return trigger_error("{$name} is not defined.");
        }
        throw new RuntimeException("{$name} is not defined.");
    }

    /**
     * Get a raw variable from template context.
     * Associative arrays can be accessed using dot notation.
     * Variable name can contain one or more filters using the notation:
     * "grandparent.parent.child|filter1|filter2"
     *
     * @param  string $var
     * @return mixed
     * @access private
     */
    private function get($var)
    {
        $data = $this->template()->data();
        if (empty($data)) {
            return ['data' => null, 'filters' => []];
        }
        $filters = explode('|', $var);
        $where = explode('.', array_shift($filters));

        return ['data' => $this->getIn($data, $where, true), 'filters' => $filters];
    }

    /**
     * @param $default
     * @return mixed|string
     */
    private function returnDefault($default)
    {
        if ($default instanceof Closure) {
            ob_start();
            $return = call_user_func($default);
            $buffer = ob_get_clean();
            $default = empty($return) ? $buffer : $return;
        }

        return $default;
    }
}
