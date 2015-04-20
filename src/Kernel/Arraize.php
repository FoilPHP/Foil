<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Kernel;

use Traversable;
use JsonSerializable;

/**
 * Stateless class that recursively convert an array or a traversable object into a nested array.
 * Optionally convert all "atomic" items to strings and optionally HTML-encode all strings.
 * Array and traversable objects are all recursively converted.
 * Non-traversable objects are converted to array, in 1st available among following methods:
 *  - if a transformer callback is provided, than it's called with object as param
 *  - if a transformer class is provided, than its transform() method is called with object as
 *  param
 *  - if a transformer object is provided, than its transform() method is called with object as
 *  param
 *  - if the object has a method toArray() it is called
 *  - if the object has a method asArray() it is called
 *  - if the object is an instance of JsonSerializable then jsonSerialize() is called
 *  - get_object_vars() is called
 * The obtained array is recursively parsed.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Arraize
{
    const CAST   = 64;
    const ESCAPE = 128;

    /**
     * @param  mixed $data     Data to convert
     * @param  bool  $escape   Should strings in data be HTML-encoded?
     * @param  array $trasf    Transformers: full qualified class names, objects or callables
     * @param  bool  $tostring Should all scalar items in data be casted to strings?
     * @return array
     */
    public function run($data = [], $escape = false, array $trasf = [], $tostring = false)
    {
        $collect = $this->collect($data, $this->flags($escape, $tostring), $trasf);

        return empty($collect) ? [] : (array) $collect;
    }

    /**
     * @param  mixed $var
     * @param  int   $flags
     * @param  array $trasf
     * @return mixed
     */
    private function collect($var, $flags, $trasf)
    {
        return $this->traversable($var)
            ?
            $this->walk($var, $flags, $trasf)
            :
            $this->atomic($var, $flags, $trasf);
    }

    /**
     * @param  mixed $var
     * @param  int   $flags
     * @param  array $trasf
     * @return mixed
     */
    private function atomic($var, $flags, $trasf)
    {
        return is_object($var)
            ?
            $this->collect($this->transform($var, get_class($var), $trasf), $flags, $trasf)
            :
            $this->escape($var, $flags);
    }

    /**
     *
     * @param  array|Traversable $var
     * @param  int               $flags
     * @param  array             $trasf
     * @return array
     */
    private function walk($var, $flags, $trasf)
    {
        $output = [];
        foreach ($var as $index => $item) {
            $output[$index] = $this->traversable($item)
                ?
                $this->collect($item, $flags, $trasf)
                :
                $this->atomic($item, $flags, $trasf);
        }

        return $output;
    }

    /**
     * @param  object $var
     * @param  string $class
     * @param  array  $trasf
     * @return array
     */
    private function transform($var, $class, $trasf)
    {
        $cb = $this->transformer(isset($trasf[trim($class, '\\')]) ? $trasf[$class] : false);

        if (is_object($cb) && method_exists($cb, 'transform')) {
            $cb = [$cb, 'transform'];
        }

        return is_callable($cb) ? $this->vars(call_user_func($cb, $var),
            false) : $this->convert($var);
    }

    /**
     * @param  mixed                $transformer
     * @return callable|object|bool
     */
    private function transformer($transformer)
    {
        if (is_string($transformer) && class_exists($transformer)) {
            $transformer = new $transformer();
        }

        return is_object($transformer) || is_callable($transformer) ? $transformer : false;
    }

    /**
     * @param  object $var
     * @return array
     */
    private function convert($var)
    {
        $toarray = array_reduce(['toArray', 'asArray'], function ($obj, $name) {
            return is_object($obj) && method_exists($obj, $name) ? (array) $obj->$name() : $obj;
        }, $var);

        return is_array($toarray) ? $toarray : $this->vars($var, true);
    }

    /**
     * @param  object $var
     * @param  bool   $try_json
     * @return array
     */
    private function vars($var, $try_json)
    {
        if ($try_json && $var instanceof JsonSerializable) {
            $var = $var->jsonSerialize();
        }

        return is_object($var) ? get_object_vars($var) : (array) $var;
    }

    /**
     * @param  bool $escape
     * @param  bool $tostring
     * @return int
     */
    private function flags($escape = false, $tostring = false)
    {
        return (empty($escape) ? 0 : self::ESCAPE) | (empty($tostring) ? 0 : self::CAST);
    }

    /**
     * @param  Traversable $var
     * @return bool
     */
    private function traversable($var)
    {
        return is_array($var) || $var instanceof Traversable;
    }

    /**
     * @param  mixed $var
     * @param  int   $flags
     * @return mixed
     */
    private function escape($var, $flags)
    {
        return ($flags & self::ESCAPE) && is_string($var)
            ?
            htmlentities($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            :
            $this->scalar($var, $flags);
    }

    /**
     * @param  mixed $var
     * @param  int   $flags
     * @return mixed
     */
    private function scalar($var, $flags)
    {
        return ($flags & self::CAST) && ! is_string($var)
            ?
            $this->escape((string) $var, $flags)
            :
            $var;
    }
}
