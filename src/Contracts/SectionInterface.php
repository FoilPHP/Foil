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
 * Object that holds section feature for template inheritance.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface SectionInterface
{
    const MODE_REPLACE = 1;
    const MODE_APPEND  = 2;
    const MODE_OUTPUT  = 4;

    /**
     * Start a section definition
     */
    public function start();

    /**
     * Ends a section definition and replace section content in layout
     *
     * @return string|void
     */
    public function replace();

    /**
     * Ends a section definition and append section content to layout content
     *
     * @return string|void
     */
    public function append();

    /**
     * Ends a section definition.
     *
     * @return string|void
     */
    public function stop();

    /**
     * Return section content
     *
     * @return string
     */
    public function content();

    /**
     * Set output mode, has to be one of the mode constants.
     *
     * @param int $mode
     */
    public function setMode($mode);

    /**
     * Return output mode, that is one of the mode constants or false.
     *
     * @return int|boolean
     */
    public function mode();
}
