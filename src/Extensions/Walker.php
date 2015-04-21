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

/**
 * Extension that provides very short functions names to be used in template files to walk arrays
 * and output string in a very concise way
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Walker extends Base
{
    /**
     * @inheritdoc
     */
    public function provideFunctions()
    {
        return [
            'walk'       => [$this, 'walk'],
            'w'          => [$this, 'walk'],
            'walkIf'     => [$this, 'walkIf'],
            'wif'        => [$this, 'walkIf'],
            'walkWrap'   => [$this, 'walkWrap'],
            'ww'         => [$this, 'walkWrap'],
            'walkWrapIf' => [$this, 'walkWrapIf'],
            'wwif'       => [$this, 'walkWrapIf']
        ];
    }

    /**
     * @param  array|\Traversable $var
     * @param  string             $format
     * @return string
     */
    public function walk($var, $format = '%s')
    {
        $out = '';
        $args = $this->args(func_get_args(), 2);
        $what = is_string($var) ? $this->api()->run('raw', $var) : $var;
        foreach ($this->api()->arraize($what, $this->option('autoescape'), [], true) as $value) {
            $replacement = is_array($value) ? $value : [$value];
            $out .= vsprintf($format, array_merge($replacement, $args));
        }

        return $out;
    }

    /**
     * @param  array|\Traversable $var
     * @param  callable|mixed     $condition
     * @param  string             $format
     * @return string
     */
    public function walkIf($var, $condition, $format = '%s')
    {
        $should = is_callable($condition) ? call_user_func($condition) : ! empty($condition);
        $out = '';
        if ($should) {
            $args = array_merge([$var, $format], $this->args(func_get_args(), 3));
            $out = call_user_func_array([$this, 'walk'], $args);
        }

        return $out;
    }

    /**
     * @param  array|\Traversable $var
     * @param  string             $wrap
     * @param  string             $format
     * @return string
     */
    public function walkWrap($var, $wrap, $format = '%s')
    {
        if (! is_string($wrap) || substr_count($wrap, '%s') !== 1) {
            $wrap = '%s';
        }
        $args = array_merge([$var, $format], $this->args(func_get_args(), 3));
        $walk = call_user_func_array([$this, 'walk'], $args);
        $out = '';
        if (! empty($walk)) {
            $out = sprintf($wrap, $walk);
        }

        return $out;
    }

    /**
     * @param  array|\Traversable $var
     * @param  callable|mixed     $condition
     * @param  string             $wrap
     * @param  string             $format
     * @return string
     */
    public function walkWrapIf($var, $condition, $wrap, $format = '%s')
    {
        $should = is_callable($condition) ? call_user_func($condition) : ! empty($condition);
        $out = '';
        if ($should) {
            $args = array_merge([$var, $wrap, $format], $this->args(func_get_args(), 4));
            $out = call_user_func_array([$this, 'walkWrap'], $args);
        }

        return $out;
    }

    /**
     * @param  array $func_args
     * @param  int   $slice
     * @return array
     */
    private function args(array $func_args, $slice = 2)
    {
        $args = array_filter(array_slice($func_args, $slice), 'is_scalar');

        return $this->option('autoescape') ? $this->api()->entities($args) : $args;
    }
}
