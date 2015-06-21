<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Blocks;

use SplStack;
use InvalidArgumentException;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class Blocks
{
    /**
     * @var array
     */
    private $blocks;

    /**
     * @var \SplStack
     */
    private $buffers;

    /**
     * @var \Foil\Blocks\Factory
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param \Foil\Blocks\Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->blocks = [];
        $this->buffers = new SplStack();
        $this->factory = $factory;
    }

    /**
     * @param  string      $name
     * @param  callable    $callback
     * @param  string|null $class
     * @return $this
     */
    public function add($name, callable $callback, $class = null)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException('Foil block name must be in a string.');
        }
        $this->blocks[$name] = $this->factory->factory($callback, $class);

        return $this;
    }

    /**
     * Open given block if it is valid.
     *
     * @param string $name
     * @param array  $args
     */
    public function open($name, array $args = [])
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException('Foil block name must be in a string.');
        }
        if (! array_key_exists($name, $this->blocks)) {
            throw new InvalidArgumentException("{$name} is not a registered Foil block.");
        }
        /** @var \Foil\Blocks\Block $buffer */
        $buffer = $this->blocks[$name];
        $buffer->open($args);
        $this->buffers->push([$buffer, $name]);
    }

    /**
     * Close current block.
     *
     * @param  string|null $which
     * @return string
     */
    public function close($which = null)
    {
        if (! $this->buffers->count()) {
            throw new LogicException('It is not possible to close a never opened block.');
        }
        list($buffer, $name) = $this->buffers->pop();
        if (! is_null($which) && $which !== $name) {
            throw new InvalidArgumentException(
                "Please close blocks in order: you need to close other block(s) before \"{$which}\"."
            );
        }
        $output = $buffer instanceof Block ? $buffer->close() : '';

        return $output;
    }
}
