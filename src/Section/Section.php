<?php namespace Foil\Section;

use Foil\Contracts\SectionInterface;
use LogicException;

/**
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Section implements SectionInterface
{
    private $content = '';
    private $mode;
    private $default_mode;
    private $started = false;
    private static $modes = [self::MODE_APPEND, self::MODE_OUTPUT, self::MODE_REPLACE];

    public function __construct($output_mode = false, $default_mode = null)
    {
        if ($output_mode !== false && ! in_array($output_mode, self::$modes, true)) {
            $output_mode = self::MODE_OUTPUT;
        }
        $this->mode = $output_mode;
        if (in_array($default_mode, self::$modes, true)) {
            $this->default_mode = $default_mode;
        }
    }

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
        if (!$this->started()) {
            throw new LogicException('You need to start a section before to end it.');
        }
        $this->started = false;
        if (empty($this->mode)) {
            $this->mode = self::MODE_REPLACE;
        }
        $this->content = ob_get_clean();
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
        if (!$this->started()) {
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
        $this->default_mode === self::MODE_REPLACE ? $this->replace() : $this->append();
    }

    public function content()
    {
        return $this->content;
    }

    public function setMode($mode, $merge = false)
    {
        if (! in_array($mode, self::$modes, true)) {
            $mode = self::MODE_OUTPUT;
        }
        $this->mode = $merge ? ($this->mode | $mode) : $mode;
    }

    public function mode()
    {
        return $this->mode;
    }

    public function started()
    {
        return $this->started;
    }
}
