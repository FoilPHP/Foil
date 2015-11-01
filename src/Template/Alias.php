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

use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
final class Alias
{
    const REGEX = '#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$#';

    /**
     * @var string
     */
    private $alias = '';

    /**
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->validate($alias);
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->alias;
    }

    /**
     * @param $alias
     */
    private function validate($alias)
    {
        if (! is_string($alias) || ! preg_match(self::REGEX, $alias)) {
            throw new InvalidArgumentException('Alias must be a valid variable name.');
        }
    }
}
