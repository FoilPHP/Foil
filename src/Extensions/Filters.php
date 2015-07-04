<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;
use InvalidArgumentException;

/**
 * Inspired by the Assets extension on Plates http://platesphp.com/extensions/asset/
 * Allow to output regular and "cache busted" assets urls using relative paths.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Filters implements ExtensionInterface
{
    /**
     * @var array
     */
    private $args;

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function setup(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function provideFilters()
    {
        return [
            'first' => [$this, 'first'],
            'last'  => [$this, 'last'],
            'chunk' => [$this, 'chunk']
        ];
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function provideFunctions()
    {
        return [
            'isFirst' => [$this, 'isFirst'],
            'isLast'  => [$this, 'isLast'],
            'chunk'   => [$this, 'chunk'],
            'index'   => [$this, 'index'],
            'index0'  => [$this, 'index0'],
        ];
    }

    /**
     * @param  string|array $data
     * @return mixed|string
     */
    public function first($data)
    {
        if (! is_array($data) && ! is_string($data)) {
            throw new InvalidArgumentException(
                'Is possible to get first item only for strings and arrays'
            );
        }
        if (is_array($data)) {
            $data = array_values($data);
        }

        return $data[0];
    }

    /**
     * @param  string|array $data
     * @param  mixed        $which
     * @return bool
     */
    public function isFirst($data, $which)
    {
        if (! is_array($data) && ! is_string($data)) {
            throw new InvalidArgumentException(
                'Is possible to get first item only for strings and arrays'
            );
        }

        return $this->first($data) === $which;
    }

    /**
     * @param  string|array $data
     * @return mixed|string
     */
    public function last($data)
    {
        if (! is_array($data) && ! is_string($data)) {
            throw new InvalidArgumentException(
                'Is possible to get last item only for strings and arrays'
            );
        }

        return is_array($data) ? end($data) : substr($data, strlen($data) - 1);
    }

    /**
     * @param  string|array $data
     * @param  mixed        $which
     * @return bool
     */
    public function isLast($data, $which)
    {
        if (! is_array($data) && ! is_string($data)) {
            throw new InvalidArgumentException(
                'Is possible to get last item only for strings and arrays'
            );
        }

        return $this->last($data) === $which;
    }

    /**
     * @param  array           $data
     * @param  mixed           $value
     * @param  null|int|string $index
     * @return bool|int|mixed
     */
    public function index(array $data, $value, $index = null)
    {
        $search = array_search($value, array_values($data));
        $i = $search !== false ? $search + 1 : -1;

        return is_null($index) ? $i : $i === $index;
    }

    /**
     * @param  array           $data
     * @param  mixed           $value
     * @param  null|int|string $index
     * @return bool|int|mixed
     */
    public function index0(array $data, $value, $index = null)
    {
        $search = array_search($value, array_values($data));
        $i = $search !== false ? $search : -1;

        return is_null($index) ? $i : $i === $index;
    }

    /**
     * @param  array $data
     * @param  int   $number
     * @param  null  $fill
     * @return array
     */
    public function chunk(array $data, $number, $fill = null)
    {
        if (! is_int($number)) {
            throw new InvalidArgumentException(
                'You must provide a number of pieces to chunk the array'
            );
        }
        $chunks = array_chunk($data, $number);
        if (is_null($fill)) {
            return $chunks;
        }
        $diff = count($data) % $number;
        if ($diff > 0) {
            $fill = array_fill($diff, $number - $diff, $fill);
            $chunks[] = array_merge(array_pop($chunks), $fill);
        }

        return $chunks;
    }
}
