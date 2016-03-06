<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Section;

use Foil\Contracts\SectionInterface;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Section implements SectionInterface
{
    private static $modes = [self::MODE_APPEND, self::MODE_OUTPUT, self::MODE_REPLACE];

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var int
     */
    private $mode;

    /**
     * @var int
     */
    private $defaultMode;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @param bool     $outputMode
     * @param null|int $defaultMode
     */
    public function __construct($outputMode = false, $defaultMode = null)
    {
        if ($outputMode !== false && ! in_array($outputMode, self::$modes, true)) {
            $outputMode = self::MODE_OUTPUT;
        }
        $this->mode = $outputMode;
        if (in_array($defaultMode, self::$modes, true)) {
            $this->defaultMode = $defaultMode;
        }
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        $this->started = true;
        ob_start();
    }

    /**
     * Close a section collecting buffer content.
     * If mode is not set, set to replace. If mode is output echo buffer.
     */
    public function replace()
    {
        if (! $this->started()) {
            throw new LogicException('You need to start a section before to end it.');
        }
        $this->started = false;
        if (empty($this->mode)) {
            $this->mode = self::MODE_REPLACE;
        }
        $buffer = ob_get_clean();
        $this->content = ($this->mode & self::MODE_APPEND) ? $buffer.$this->content : $buffer;
        if ($this->mode & self::MODE_OUTPUT) {
            echo $this->content();
        }
    }

    /**
     * Close a section collecting buffer content.
     * If in replace mode ignore buffer otherwise append buffer to current content.
     * If mode is not set, set to append.
     * If mode is output echo content.
     */
    public function append()
    {
        if (! $this->started()) {
            throw new LogicException('You need to start a section before to end it.');
        }
        $this->started = false;
        if (empty($this->mode)) {
            $this->mode = self::MODE_APPEND;
        }
        $buffer = ob_get_clean();
        $this->content = ($this->mode & self::MODE_REPLACE) ? $this->content : $buffer.$this->content;
        if ($this->mode & self::MODE_OUTPUT) {
            echo $this->content();
        }
    }

    /**
     * Close a section collecting buffer content.
     * If in replace mode ignore buffer otherwise append buffer to current content.
     * If mode is not set, set to append.
     * If mode is output echo content.
     */
    public function stop()
    {
        $this->defaultMode === self::MODE_REPLACE ? $this->replace() : $this->append();
    }

    /**
     * @inheritdoc
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function setMode($mode, $merge = false)
    {
        if (! in_array($mode, self::$modes, true)) {
            $mode = self::MODE_OUTPUT;
        }
        $this->mode = $merge ? ($this->mode | $mode) : $mode;
    }

    /**
     * @inheritdoc
     */
    public function mode()
    {
        return $this->mode;
    }

    /**
     * @return bool
     */
    public function started()
    {
        return $this->started;
    }
}
