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
use Foil\Kernel\Command;
use Foil\Kernel\Escaper;

/**
 * Extension that provides very short functions names to be used in template files to walk arrays
 * and output string in a very concise way
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Walker implements ExtensionInterface
{
    /**
     * @var array
     */
    protected $args;

    /**
     * @var \Foil\Kernel\Command
     */
    private $command;

    /**
     * @var \Foil\Kernel\Escaper
     */
    private $escaper;

    /**
     * @var array
     */
    private $options;

    /**
     * @param \Foil\Kernel\Command $command
     * @param \Foil\Kernel\Escaper $escaper
     * @param array                $options
     */
    public function __construct(Command $command, Escaper $escaper, array $options)
    {
        $this->command = $command;
        $this->escaper = $escaper;
        $this->options = $options;
    }

    /**
     * Setup the extension using an arguments array that should be provided on registration
     *
     * @param array $args
     * @codeCoverageIgnore
     */
    public function setup(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function provideFilters()
    {
        return [];
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
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
        $what = is_string($var) ? $this->command->run('raw', $var) : $var;
        foreach (\Foil\arraize($what, $this->options['autoescape'], [], true) as $value) {
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
     * @param  array $funcArgs
     * @param  int   $slice
     * @return array
     */
    private function args(array $funcArgs, $slice = 2)
    {
        $args = array_filter(array_slice($funcArgs, $slice), 'is_scalar');

        return $this->options['autoescape'] ? $this->escaper->escape($args) : $args;
    }
}
