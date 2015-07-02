<!--
currentMenu: "linkhelpers"
currentSection: "Blocks & Helpers"
title: "Link Helpers"
-->

# Link Helpers

In templates, absolute and relative urls have its own pro and cons to be used for internal links.

Relative links are more *portable* and require less typing, but sometimes they can't work (e.g in case `mod_rewrite` or similar is used to handle urls).

Foil link helpers are functions that allow to output internal links taking the best from both absolute and relative links.

There are just 2 helpers:

- `link()`
- `asset()`

Link helpers are shipped with Foil but they are **not** enabled by default.

To enable them you need to load an extension that is shipped with Foil (more on Foil extensions under *"Extending Foil"* docs section).

```php
$engine->loadExtension(new \Foil\Extensions\Links(), $args);
```

`$args` is an array of configurations that changes how the helpers will work. More on this below.

## `link()`

This helper provides 2 benefits:

 - allow to easily output long urls with few chars
 - allow to write absolute urls typing only the relative part

There are 3 options that regulate how helper work, they are:

- `'host'` (string) Is the base host. Used to output absolute urls only typing the relative part.
 Optional, if not provided the helper will use the current host.
- `'scheme'` (string) Is the default scheme ('http', 'https'..) to be used for links.
 When set to `false` urls will use no scheme, starting with `//:`.
 Optional, if not provided the helper will use the current scheme.
 Note that this is the *default* scheme, but is possible to use a different scheme for any url.
- `'urls'` (array) A set of default relative paths, in a way that is possible to output very long urls with few chars.

Assuming the extension is loaded like so:

```php
$args = [
    'host'   => 'example.com',
    'scheme' => 'http',
    'urls'   => [
        'admin' => '/path/to/admin',
        'blog'  => '/path/to/blog'
    ],
];
$engine->loadExtension(new \Foil\Extensions\Links(), $args);
```

In a template:

```php
$this->link('foo'); // "http://example.com/foo"

$this->link('profile', 'admin'); // "http://example.com/path/to/admin/foo"

$this->link('dashboard', 'admin', 'https'); // "https://example.com/path/to/admin/foo"

$this->link('2014/hello-world', 'blog'); // "http://example.com/path/to/blog/2014/hello-world"
```

Nice thing is that to configure different urls for different environments or to change host is just a matter of change a singular option with no need to change templates.

## `asset()`

This helper is similar in purpose to `link()` but it's developed with specific functions for assets (styles, scripts and images).

Configuration for assets is independent from configuration for urls, so if your assets are in CDN your life will be easy when you'll need to output that long urls.

There are 4 options that regulate how `asset()` work, they are:

- `'assets_host'` (string) Base host to be used for assets. Is independent from regular urls host for maximum flexibility. Optional, default to `'host'` option.
- `'assets_url'` (string) This is the relative to `'assets_host'` part of assets url. Optional, no default.
- `'assets_path'` (string) This is the base filesystem path to your assets. This is needed when cache bust is used to read assets files last update time.  
- `'cache_bust'` (mixed) This allow *cache busted* assets.
 This feature is largely inspired to [Plates asset extension](http://platesphp.com/extensions/asset/) and it works by appending the timestamp of the file last update to its URL.
 For example, `/css/style.css` becomes `/css/style.1421281020.css`

 Please note that you need to properly configure `'assets_path'` and **setup your HTTP server** to use this function.

 For server configuration see examples on [Plates site](http://platesphp.com/extensions/asset/#filename-caching).

 In Foil you can configure cache bust for:
 - all assets, by set this option to `true` or `false`
 - specific kind of assets, by set this option to `'images'` or `'styles'` or `'scripts'`
 - specific file extensions, by set this option to an array of supported extensions, e.g. `['css','js']`

Default HTTP scheme configuration for assets is configured via `'scheme'` option, the same used for `links()`.

Assuming the extension is loaded like so:

```php
$args = [
  'assets_host' => 'static.example.com',
  'assets_url'  => '/assets',
  'scheme'      => false,
  'assets_path' => dirname(__DIR__).'/assets',
  'cache_bust'  => ['css','js'],
];
$engine->loadExtension(new \Foil\Extension\Links(), $args);
```
In a template:

```php
$this->asset('images/foo.jpg'); // "//static.example.com/assets/images/foo.jpg"

$this->asset('images/foo.jpg', 'http'); // "http://static.example.com/assets/images/foo.jpg"

$this->asset('css/style.css'); // "//static.example.com/assets/css/style.1421281020.css"

$this->asset('js/custom.js'); // "//static.example.com/assets/js/custom.1423959420.js"
```

Remember that you need to configure your HTTP server to use the cache bust feature.
