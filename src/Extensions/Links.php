<?php namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;

/**
 * Inspired by the Assets extension on Plates http://platesphp.com/extensions/asset/
 * Allow to easily output generic urls and also regular and "cache busted"
 * assets urls.
 * Both kind of urls can be relative or absolute, setting a domain.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Links implements ExtensionInterface
{
    private $asset_path = '';
    private $asset_url = '/';
    private $urls = [];
    private $host;
    private $assets_host;
    private $scheme = 'http://';
    private $cache_bust = false;

    public function setup(array $args = [])
    {
        $this->setupHost($args);
        $this->setupHost($args, 'assets_host');
        $this->host and $this->setupScheme($args);
        $this->setupUrls($args);
        $this->setupCache($args);
        $this->setupAssetPaths($args);
    }

    public function provideFilters()
    {
        return [];
    }

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
     * @param  string|bool $subdir_url
     * @param  boolean     $use_scheme
     * @return string
     */
    public function link($file, $subdir_url = false, $use_scheme = null)
    {
        $sub = '/';
        $clean = $this->clean($file, false);
        if (is_string($subdir_url) && array_key_exists(strtolower($subdir_url), $this->urls)) {
            $sub = $this->urls[strtolower($subdir_url)];
        }

        return $this->addHost($sub.$clean, $use_scheme, false);
    }

    /**
     * Return a asset url. If cache burst is enabled
     * file last modified timestamp is appended to real file name.
     * When no host is set url is relative, otherwise is absolute. In the latter case is possible
     * to use different schemes on a per-url basis.
     *
     * @param  string  $asset
     * @param  boolean $use_scheme
     * @return string
     */
    public function asset($asset, $use_scheme = null)
    {
        $suffix = false;
        $ext = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
        if (is_array($this->cache_bust) && in_array($ext, $this->cache_bust, true) && is_string($this->asset_path)) {
            $path = $this->asset_path.DIRECTORY_SEPARATOR.$this->normalize($asset);
            $suffix = is_readable($path) ? @filemtime($path) : false;
        }
        if ($suffix) {
            $asset = substr($asset, 0, -1 * strlen($ext)).$suffix.'.'.$ext;
        }

        return $this->addHost($this->asset_url.$asset, $use_scheme, true);
    }

    private function setupHost($args, $which = 'host')
    {
        if (! isset($args[$which]) || is_null($args[$which])) {
            $this->$which = $which === 'host' ? false : null;
        } elseif (empty($args[$which])) {
            $this->$which = false;
        } elseif (is_string($args[$which]) && strtolower($args[$which]) !== 'auto') {
            $parse = parse_url($args[$which]);
            $host = isset($parse['host']) ? $parse['host'] : $parse['path'];
            $this->$which = $this->clean($host);
        } elseif ($args[$which] === true || is_string($args[$which])) {
            $this->$which = $this->clean(filter_input(INPUT_SERVER, 'SERVER_NAME'));
        }
    }

    private function setupScheme($args)
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

    private function setupUrls($args)
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
            $this->cache_bust = array_merge($def['images'], $def['styles'], $def['scripts']);
        } elseif (is_array($args['cache_bust'])) {
            $this->cache_bust = array_map([$this, 'cleanExt'], array_filter($args['cache_bust'], 'is_string'));
        } elseif (
            is_string($args['cache_bust'])
            && array_key_exists(strtolower($args['cache_bust']), $def)
        ) {
            $this->cache_bust = $def[strtolower($args['cache_bust'])];
        }
    }

    private function setupAssetPaths($args)
    {
        $this->asset_url = isset($args['assets_url']) && is_string($args['assets_url']) ?
            '/'.$this->clean($args['assets_url']).'/' :
            '/';
        if (! is_array($this->cache_bust)) {
            return;
        }
        $this->asset_path = isset($args['assets_path']) ?
            rtrim($this->normalize($args['assets_path']), '/\\') :
            false;
    }

    private function normalize($path)
    {
        return preg_replace('|[\\/]+|', DIRECTORY_SEPARATOR, filter_var($path, FILTER_SANITIZE_URL));
    }

    private function clean($url, $lower = true)
    {
        $trim = trim(filter_var((string) $url, FILTER_SANITIZE_URL), '/');

        return $lower ? strtolower($trim) : $trim;
    }

    private function cleanExt($ext)
    {
        return strtolower(preg_replace('/[^a-zA-Z]/', '', $ext));
    }

    private function isScheme($scheme)
    {
        return is_string($scheme) && in_array(strtolower($scheme), ['https', 'http'], true);
    }

    private function addHost($url, $use_scheme, $is_asset)
    {
        $host = $is_asset && ! is_null($this->assets_host) ? $this->assets_host : $this->host;
        if (! $host) {
            return $url;
        }
        $scheme = '//';
        if ($use_scheme !== false) {
            $scheme = $this->isScheme($use_scheme) ? strtolower($use_scheme).'://' : $this->scheme;
        }

        return $scheme.$host.'/'.ltrim($url, '\\/');
    }
}
