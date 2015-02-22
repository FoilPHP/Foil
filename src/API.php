<?php namespace Foil;

/**
 * YOLO
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 * @method mixed foil()
 * @method Engine engine()
 * @method string renderTemplate()
 * @method mixed option()
 * @method void addContext()
 * @method void addGlobalContext()
 * @method void addContextUsing()
 * @method mixed run()
 * @method void fire()
 * @method void on()
 * @method mixed entities()
 * @method mixed decode()
 * @method array arraize()
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
