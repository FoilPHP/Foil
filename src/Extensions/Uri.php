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

/**
 * Inspired by the URI extension on Plates http://platesphp.com/extensions/uri/
 * Allow to do conditional tasks based on current url.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Uri implements ExtensionInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $chunks;

    /**
     * @inheritdoc
     */
    public function setup(array $args = [])
    {
        $path = isset($args['pathinfo'])
            ? $args['pathinfo']
            : filter_input(INPUT_SERVER, 'PATH_INFO');
        $this->path = $this->clean($path);
        $home = isset($args['home']) && ! is_null($args['home'])
            ? $this->clean($args['home'])
            : false;
        if ($home && strpos($this->path, $home) === 0) {
            $this->path = $this->clean(substr($this->path, strlen($home)));
        }
        if (empty($this->path)) {
            $this->path = '/';
        }
        $this->chunks = $this->path !== '/' ? explode('/', $this->path) : ['/'];
    }

    /**
     * @inheritdoc
     */
    public function provideFilters()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function provideFunctions()
    {
        return [
            'uri' => function () {
                return $this;
            }
        ];
    }

    /**
     * Check if a given url match current url.
     * Is possible to specify url using a stringUri or an array where 1st item is url chunk number
     * and 2nd item is the chunk to match.
     *
     * @param  string|array $url
     * @param  mixed        $if_true  What return if the compare is true
     * @param  mixed        $if_false What return if the compare is true
     * @return mixed
     */
    public function is($url = '', $if_true = true, $if_false = "")
    {
        if (is_array($url) && isset($url[0]) && is_int($url[0]) && $url[0] <= count($this->chunks)) {
            $chunk = isset($url[1]) && is_string($url[1]) ? $this->clean($url[1]) : '';

            return $this->clean($this->chunks[($url[0] - 1)]) === $chunk ? $if_true : $if_false;
        }

        return $this->clean($this->path) === $this->clean($url) ? $if_true : $if_false;
    }

    /**
     * Check if current url starts with a given url.
     *
     * @param  string $url
     * @param  mixed  $if_true  What return if the compare is true
     * @param  mixed  $if_false What return if the compare is true
     * @return mixed
     */
    public function has($url = '', $if_true = true, $if_false = "")
    {
        return strpos($this->path, $this->clean($url)) === 0 ? $if_true : $if_false;
    }

    /**
     * Check if current url matches a given regex.
     *
     * @param  string $regex
     * @param  mixed  $if_true  What return if the compare is true
     * @param  mixed  $if_false What return if the compare is true
     * @return mixed
     */
    public function match($regex = '', $if_true = true, $if_false = "")
    {
        return preg_match("~{$regex}~", $this->path) === 1 ? $if_true : $if_false;
    }

    /**
     * @param  string $url
     * @return string
     */
    private function clean($url)
    {
        if (! is_string($url)) {
            $url = '';
        }

        return strtolower(trim(filter_var($url, FILTER_SANITIZE_URL), '/'));
    }
}
