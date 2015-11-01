<?php namespace Foil\Tests;

use JsonSerializable;

class Value
{

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}

class ToArray extends Value
{

    public function toArray()
    {
        return ['toarray' => (array)$this->value];
    }
}

class AsArray extends Value
{

    public function asArray()
    {
        return ['asarray' => (array)$this->value];
    }
}

class Json implements JsonSerializable
{

    public function jsonSerialize()
    {
        return '<b>I am JSON</b>';
    }
}

class Target extends Value
{

}

class Transformer
{

    public function transform($object)
    {
        return is_object($object) ? ['transformed' => get_object_vars($object)] : false;
    }
}
