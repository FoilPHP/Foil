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
 * Inspired by the Assets extension on Plates http://platesphp.com/extensions/asset/
 * Allow to easily output generic urls and also regular and "cache busted"
 * assets urls.
 * Both kind of urls can be relative or absolute, setting a domain.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Links implements ExtensionInterface
{
    /**
     * @var string
     */
    private $assetPath = '';

    /**
     * @var string
     */
    private $assetUrl = '/';

    /**
     * @var array
     */
    private $urls = [];

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $assetsHost;

    /**
     * @var string
     */
    private $scheme = 'http://';

    /**
     * @var bool
     */
    private $cacheBust = false;

    /**
     * @inheritdoc
     */
    public function setup(array $args = [])
    {
        $this->setupHost($args);
        $this->setupHost($args, 'assets_host');
        $this->host and $this->setupScheme($args);
        $this->setupUrls($args);
        $this->setupCache($args);
        $this->setupAssetPaths($args);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
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
            'asset' => [$this, 'asset'],
            'link'  => [$this, 'link']
        ];
    }

    /**
     * Return a relative (or absolute if a host is set) url for a file whose directory has been
     * set via setup arguments. Allow to easily output long urls with few chars.
     *
     * @param  string      $file
     * @param  string|bool $subdirUrl
     * @param  boolean     $scheme
     * @return string
     */
    public function link($file, $subdirUrl = false, $scheme = null)
    {
        $sub = '/';
        $clean = $this->clean($file, false);
        if (is_string($subdirUrl) && array_key_exists(strtolower($subdirUrl), $this->urls)) {
            $sub = $this->urls[strtolower($subdirUrl)];
        }

        return $this->addHost($sub.$clean, $scheme, false);
    }

    /**
     * Return a asset url. If cache burst is enabled
     * file last modified timestamp is appended to real file name.
     * When no host is set url is relative, otherwise is absolute. In the latter case is possible
     * to use different schemes on a per-url basis.
     *
     * @param  string  $asset
     * @param  boolean $scheme
     * @return string
     */
    public function asset($asset, $scheme = null)
    {
        $suffix = false;
        $ext = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
        if (is_array($this->cacheBust) && in_array($ext, $this->cacheBust,
                true) && is_string($this->assetPath)
        ) {
            $path = $this->assetPath.DIRECTORY_SEPARATOR.$this->normalize($asset);
            $suffix = is_readable($path) ? @filemtime($path) : false;
        }
        if ($suffix) {
            $asset = substr($asset, 0, -1 * strlen($ext)).$suffix.'.'.$ext;
        }

        return $this->addHost($this->assetUrl.$asset, $scheme, true);
    }

    /**
     * @param array  $args
     * @param string $which
     */
    private function setupHost(array $args, $which = 'host')
    {
        $checks = [
            function (array $args, $which) {
                return ! isset($args[$which]) || is_null($args[$which]);
            },
            function (array $args, $which) {
                return empty($args[$which]);
            },
            function (array $args, $which) {
                return is_string($args[$which]) && strtolower($args[$which]) !== 'auto';
            },
            function (array $args, $which) {
                return $args[$which] === true || is_string($args[$which]);
            },
        ];
        $actions = [
            function ($var, $which) {
                $this->$var = $which === 'host' ? false : null;
            },
            function ($var) {
                $this->$var = false;
            },
            function ($var, $which, array $args) {
                $parse = parse_url($args[$which]);
                $host = isset($parse['host']) ? $parse['host'] : $parse['path'];
                $this->$var = $this->clean($host);
            },
            function ($var) {
                $this->$var = $this->clean(filter_input(INPUT_SERVER, 'SERVER_NAME'));
            },
        ];

        foreach ($checks as $i => $check) {
            if ($check($args, $which)) {
                $args = [
                    $which === 'assets_host' ? 'assetsHost' : 'host',
                    $which,
                    $args,
                ];
                call_user_func_array($actions[$i], $args);
                break;
            }
        }
    }

    /**
     * @param array $args
     */
    private function setupScheme(array $args)
    {
        if (! isset($args['scheme']) || is_null($args['scheme'])) {
            $secure = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING);
            $this->scheme = ! empty($secure) && $secure !== 'off' ? 'https://' : 'http://';
        } elseif ($this->isScheme($args['scheme'])) {
            $this->scheme = strtolower($args['scheme']).'://';
        } elseif ($args['scheme'] === false) {
            $this->scheme = '//';
        }
    }

    /**
     * @param array $args
     */
    private function setupUrls(array $args)
    {
        if (! isset($args['urls']) || ! is_array($args['urls'])) {
            return;
        }
        array_walk($args['urls'], function ($dir, $name) {
            if (is_string($name)) {
                $this->urls[strtolower($name)] = '/'.$this->clean($dir, false).'/';
            }
        });
    }

    /**
     * @param array $args
     */
    private function setupCache($args)
    {
        if (! isset($args['cache_bust']) || is_null($args['cache_bust'])) {
            return;
        }
        $def = [
            'images'  => ['jpg', 'jpeg', 'gif', 'png', 'svg'],
            'styles'  => ['css'],
            'scripts' => ['js'],
        ];
        if ($args['cache_bust'] === true || $args['cache_bust'] === 'all') {
            $this->cacheBust = array_merge($def['images'], $def['styles'], $def['scripts']);
        } elseif (is_array($args['cache_bust'])) {
            $this->cacheBust = array_map([$this, 'cleanExt'],
                array_filter($args['cache_bust'], 'is_string'));
        } elseif (
            is_string($args['cache_bust'])
            && array_key_exists(strtolower($args['cache_bust']), $def)
        ) {
            $this->cacheBust = $def[strtolower($args['cache_bust'])];
        }
    }

    /**
     * @param array $args
     */
    private function setupAssetPaths($args)
    {
        $this->assetUrl = isset($args['assets_url']) && is_string($args['assets_url'])
            ? '/'.$this->clean($args['assets_url']).'/'
            : '/';
        if (! is_array($this->cacheBust)) {
            return;
        }
        $this->assetPath = isset($args['assets_path'])
            ? rtrim($this->normalize($args['assets_path']), '/\\')
            : false;
    }

    /**
     * @param  string $path
     * @return string
     */
    private function normalize($path)
    {
        return preg_replace(
            '|[\\/]+|',
            DIRECTORY_SEPARATOR,
            filter_var($path, FILTER_SANITIZE_URL)
        );
    }

    /**
     * @param  string $url
     * @param  bool   $lower
     * @return string
     */
    private function clean($url, $lower = true)
    {
        $trim = trim(filter_var((string) $url, FILTER_SANITIZE_URL), '/');

        return $lower ? strtolower($trim) : $trim;
    }

    /**
     * @param  string $ext
     * @return string
     */
    private function cleanExt($ext)
    {
        return strtolower(preg_replace('/[^a-zA-Z]/', '', $ext));
    }

    /**
     * @param  string $scheme
     * @return bool
     */
    private function isScheme($scheme)
    {
        return is_string($scheme) && in_array(strtolower($scheme), ['https', 'http'], true);
    }

    /**
     * @param  string $url
     * @param  bool   $useScheme
     * @param  bool   $asset
     * @return string
     */
    private function addHost($url, $useScheme, $asset)
    {
        $host = $asset && ! is_null($this->assetsHost) ? $this->assetsHost : $this->host;
        if (! $host) {
            return $url;
        }
        $scheme = '//';
        if ($useScheme !== false) {
            $scheme = $this->isScheme($useScheme) ? strtolower($useScheme).'://' : $this->scheme;
        }

        return $scheme.$host.'/'.ltrim($url, '\\/');
    }
}
