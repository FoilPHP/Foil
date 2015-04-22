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
 * Escaper must be able to escape strings and (even deeply nested arrays) containing strings.
 * Moreover, escapers should support different escaping strategies, e.g. "html", "js", etc..
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
interface EscaperInterface
{
    /**
     * @param  mixed       $data     Data to escape
     * @param  string      $strategy Escaping strategies, 'html', 'js', 'css', 'attr'
     * @param  string|null $encoding Character encoding
     * @return mixed
     */
    public function escape($data, $strategy = 'html', $encoding = null);

    /**
     * Is the counter part of escape() and have to support all data types supported by escape()
     * but only for 'html' strategy.
     *
     * @param  mixed       $data
     * @param  string|null $encoding
     * @return mixed
     */
    public function decode($data, $encoding = null);
}
