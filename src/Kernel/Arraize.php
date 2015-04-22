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
 * Class that recursively converts any data into an array.
 *
 * Optionally convert all "atomic" items to strings and optionally HTML-encode all strings.
 * Array and traversable objects are all recursively converted.
 * Non-traversable objects are converted to array, using 1st available among following methods:
 *  - if a transformer callback is provided, than it's called passing the object as param
 *  - if a transformer class is provided, than its transform() method is called passing the object
 *    as param
 *  - if a transformer object is provided, than its transform() method is called passing the object
 *    as param
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
    const TOSTRING = 1;
    const ESCAPE   = 2;

    /**
     * @var mixed
     */
    private $rawData;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $transformers = [];

    /**
     * @var int
     */
    private $flags = 0;

    /**
     * @var bool
     */
    private $done = false;

    /**
     * Constructor.
     *
     * Stores data to convert an options.
     *
     * @param mixed    $data         Data to convert
     * @param array    $transformers Array of object transformers
     * @param int|null $flags        Bitmask of class constants flags
     */
    public function __construct($data, array $transformers = [], $flags = null)
    {
        $this->rawData = $data;
        $this->transformers = $transformers;
        if (is_int($flags)) {
            $this->flags = $flags;
        }
    }

    /**
     * Run the conversion or just return the result if conversion was already done.
     *
     * @return array
     */
    public function __invoke()
    {
        if (! $this->done && ! empty($this->rawData)) {
            $collect = $this->collect($this->rawData);
            $this->data = empty($collect) ? [] : (array) $collect;
            $this->done = true;
        }

        return $this->data;
    }

    /**
     * Recursively called to convert items. If given item is traversable than every items is parsed
     * otherwise it is returned maybe transformed according to settings.
     *
     * @param  mixed $var
     * @return mixed
     */
    private function collect($var)
    {
        return is_array($var) || $var instanceof Traversable
            ? $this->walk($var)
            : $this->atomic($var);
    }

    /**
     * Function called for non-traversable items. If the given item is an object it is transformed,
     * otherwise returned maybe casted to string and may HTML-encoded, according to settings.
     *
     * @param  mixed $var
     * @return mixed
     */
    private function atomic($var)
    {
        return is_object($var)
            ? $this->collect($this->transform($var, get_class($var)))
            : $this->scalar($var);
    }

    /**
     * Function called for traversable items. Methods loops every item and if it is traversable
     * it is recursively parsed, otherwise it is returned maybe transformed according to settings.
     *
     * @param  array|Traversable $var
     * @return array
     */
    private function walk($var)
    {
        $output = [];
        foreach ($var as $index => $item) {
            $output[$index] = is_array($item) || $item instanceof Traversable
                ? $this->collect($item)
                : $this->atomic($item);
        }

        return $output;
    }

    /**
     * Takes an object and transform it to an array, using a transformer class or callback if
     * available, calling jsonSerialize() if the object is JsonSerializable or just getting
     * array of object vars.
     *
     * @param  object $object
     * @return array
     */
    private function transform($object)
    {
        $class = trim(get_class($object), '\\');
        $transformer = array_key_exists($class, $this->transformers)
            ? $this->transformers[$class]
            : false;
        if (! $transformer) {
            return $this->convert($object);
        }

        return is_callable(($cb = $this->transformer($transformer)))
            ? $this->forceArray(call_user_func($cb, $object))
            : $this->convert($object);
    }

    /**
     * Return a transformer callback from a settings
     *
     * @param  mixed         $transformer
     * @return callable|bool
     */
    private function transformer($transformer)
    {
        if (is_string($transformer) && class_exists($transformer)) {
            $transformer = new $transformer();
        }
        if (is_object($transformer) && method_exists($transformer, 'transform')) {
            $transformer = [$transformer, 'transform'];
        }

        return is_callable($transformer) ? $transformer : false;
    }

    /**
     * @param  object $object
     * @return array
     */
    private function convert($object)
    {
        $toArray = array_reduce(['toArray', 'asArray'], function ($obj, $name) {
            return is_object($obj) && method_exists($obj, $name) ? (array) $obj->$name() : $obj;
        }, $object);

        return is_array($toArray) ? $toArray : $this->forceArray($object);
    }

    /**
     * Return an array from any variable.
     * If the variable is an object that implements JsonSerializable calls jsonSerialize() on it
     * before returning result casted to an array.
     *
     * @param  mixed $var
     * @return array
     */
    private function forceArray($var)
    {
        if ($var instanceof JsonSerializable) {
            $var = $var->jsonSerialize();
        }

        return is_object($var) ? get_object_vars($var) : (array) $var;
    }

    /**
     * Run on every scalar items, optionally convert them to string and optionally escape strings
     * for HTML entities according to settings.
     *
     * @param  mixed $var
     * @return mixed
     */
    private function scalar($var)
    {
        if (($this->flags & self::ESCAPE) && is_string($var)) {
            return htmlentities($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return ($this->flags & self::TOSTRING) && ! is_string($var)
            ? $this->scalar((string) $var)
            : $var;
    }
}
