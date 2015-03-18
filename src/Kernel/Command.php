<?php namespace Foil\Kernel;

use InvalidArgumentException;
use Foil\Contracts\ExtensionInterface as Extension;
use Foil;

/**
 * Class that holds all the functions and filters registered in extensions.
 * It handle all non-existent methods called on template objects inside template files.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Command
{
    private $autoescape;
    private $functions = [];
    private $filters = [];
    private $safe = [];
    private $locked;

    public function __construct($autoescape = true)
    {
        $this->autoescape = ! empty($autoescape);
    }

    /**
     * Register some functions to be executed as template functions
     *
     * @param array $functions
     * @param mixed $safe      Can functions output html? Boolean or array of function names
     */
    public function registerFunctions(array $functions, $safe = false)
    {
        $this->register($functions, 'functions', $safe);
    }

    /**
     * Register some filters to be executed as template filters
     *
     * @param array $filters
     */
    public function registerFilters(array $filters)
    {
        $this->register($filters, 'filters');
    }

    /**
     * Run a registered function and return the result.
     *
     * @param  string $function
     * @return mixed
     */
    public function run($function)
    {
        $can_echo = ['stop', 'append', 'replace', 'section'];
        if (in_array($function, $can_echo, true)) {
            return call_user_func_array([$this, 'doRun'], func_get_args());
        }

        return call_user_func_array([$this, 'doRunIsolated'], func_get_args());
    }

    /**
     * Run a registered filter
     *
     * @param  string $filter
     * @param  mixed  $original Something to be filtered
     * @param  array  $args     Additional arguments for filter callback (1st is original value)
     * @return mixed
     */
    public function filter($filter, $original, array $args = [])
    {
        array_unshift($args, $original);

        return $this->execute($filter, $this->filters, $args);
    }

    /**
     * When class is locked registered callbacks can't be overridden
     */
    public function lock()
    {
        $this->locked = true;
    }

    /**
     * Register a callback
     *
     * @param  array         $callbacks
     * @param  string        $which
     * @param  boolean|array $safe
     * @return void
     */
    private function register(array $callbacks, $which, $safe = false)
    {
        $filtered = $this->allowedCallbacks($callbacks, $which);
        if (empty($filtered)) {
            return;
        }
        $this->$which = array_merge($this->$which, $filtered);
        if ($which === 'functions' && ! empty($safe)) {
            $are_safe = is_array($safe) ? array_intersect($safe, array_keys($filtered)) : array_keys($filtered);
            $this->safe = array_merge($this->safe, $are_safe);
        }
    }

    /**
     * Run a registered callback
     *
     * @param  string                   $callback
     * @param  array                    $which    functions or filters
     * @param  array                    $args     execution arguments
     * @return mixed
     * @throws InvalidArgumentException If callback is not a valid registered callback name
     */
    private function execute($callback, array $which, array $args = [])
    {
        if (! is_string($callback)) {
            throw new InvalidArgumentException('Foil callback name must be in a string.');
        }
        if (array_key_exists($callback, $which)) {
            return call_user_func_array($which[$callback], $args);
        }
        throw new InvalidArgumentException('"'.$callback.'" is not a registered Foil callback.');
    }

    /**
     * Prevent a function can echo anything
     *
     * @return mixed
     */
    private function doRunIsolated()
    {
        ob_start();
        $output = call_user_func_array([$this, 'doRun'], func_get_args());
        ob_end_clean();

        return $output;
    }

    /**
     * Run a function and autoescape returned content if autoescape is turned on
     *
     * @param  string $function Callback name
     * @return mixed
     */
    private function doRun($function)
    {
        $raw = $this->execute($function, $this->functions, array_slice(func_get_args(), 1));
        if (
            ($this->autoescape && ! in_array($function, $this->safe, true))
            && (is_array($raw) || is_string($raw) || (is_object($raw) && ! $raw instanceof Extension))
        ) {
            $raw = is_object($raw) ? '' : Foil\entities($raw);
        }

        return is_null($raw) ? '' : $raw;
    }

    /**
     * Ensure items in callback array are actually callbacks and their names aren't used by Foil
     *
     * @param  array  $callbacks
     * @param  string $which     "functions" or "filters"
     * @return array
     * @access private
     */
    private function allowedCallbacks(array $callbacks, $which)
    {
        $reserved = $this->locked ? array_flip(array_keys($this->$which)) : [];

        return array_diff_key(array_filter($callbacks, 'is_callable'), $reserved);
    }
}
