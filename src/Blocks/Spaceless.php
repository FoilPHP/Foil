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
class Spaceless
{
    /**
     * Removes spaces between open and close HTML tags.
     *
     * @param  string $output
     * @return string
     */
    public function __invoke($output)
    {
        return trim(preg_replace('/>\s+</', '><', $output));
    }
}
