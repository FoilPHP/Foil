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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
interface BlockInterface
{
    /**
     * Push a block callable into the stack and open a buffer.
     *
     * @param array $args
     */
    public function open(array $args = []);

    /**
     * Close last buffer and uses current block to process the output.
     *
     * @return string
     */
    public function close();
}
