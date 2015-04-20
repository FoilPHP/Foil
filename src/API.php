<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil;

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
    /**
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(__NAMESPACE__.'\\'.$this->name($name), $arguments);
    }

    /**
     * @param  string $str
     * @return string
     */
    private function name($str)
    {
        return ctype_lower($str) ? $str : strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $str));
    }
}
