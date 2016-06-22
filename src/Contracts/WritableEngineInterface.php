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
 * An engine able to "write" rendered content.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface WritableEngineInterface extends EngineInterface
{
    /**
     * Render the template with provided data and write result to given target.
     *
     * Being target optional, default behavior is left to implementation.
     *
     * @param string $template
     * @param array  $data
     * @param mixed  $target
     * @return bool  Return true if the write happened correctly.
     */
    public function write($template, array $data = [], $target = null);
}
