<?php namespace Foil;

/**
 * YOLO
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class API
{
    public function __call($name, $arguments)
    {
        return call_user_func_array(__NAMESPACE__.'\\'.$this->name($name), $arguments);
    }

    private function name($str)
    {
        return ctype_lower($str) ? $str : strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $str));
    }
}
