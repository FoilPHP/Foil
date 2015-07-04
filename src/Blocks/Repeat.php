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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 *
 * @codeCoverageIgnore
 */
class Repeat
{
    /**
     * @param  string $output
     * @param  int    $number
     * @return string
     */
    public function __invoke($output, $number = 1)
    {
        return str_repeat($output, $number);
    }
}
