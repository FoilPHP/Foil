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

use Foil\Contracts\BlockInterface;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class Block implements BlockInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var int
     */
    private $opened = 0;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Push a block callable into the stack and open a buffer.
     *
     * @param array $args
     */
    public function open(array $args = [])
    {
        $this->opened++;
        $this->args = $args;
        ob_start();
    }

    /**
     * Close last buffer and uses current block to process the output.
     *
     * @return string
     */
    public function close()
    {
        if ($this->opened < 1) {
            throw new LogicException('It is not possible to close a never opened block.');
        }
        $args = $this->args;
        $this->opened--;
        $this->opened === 0 and $this->args = [];
        array_unshift($args, ob_get_clean());
        $block = call_user_func_array($this->callback, $args);

        return is_string($block) ? $block : '';
    }
}
