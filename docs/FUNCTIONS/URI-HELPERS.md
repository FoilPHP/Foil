<!--
currentMenu: "urihelpers"
currentSection: "Functions"
title: "URI Helpers"
-->

# URI Helpers

Inspired to [Plates URI extension](http://platesphp.com/extensions/uri/) these helpers allow to easily deal with current url in templates.
For example to set current HTML class for a link in a menu based on the page being viewed, and so on.

URI helpers are shipped with Foil but they are **not** enabled by default.

To enable them you need to load an extension that is shipped with Foil (more on Foil extensions under *"Extending Foil"* docs section).

```php
$engine->loadExtension(new \Foil\Extensions\Uri(), $args);
```

The `$args` is an array of configurations that changes how the helpers will work. More on this below.

## Pathinfo

By default the extension will retrieve path info information using `$_SERVER` superglobal variable, but, for any reason, you might set a custom path info using `'pathinfo'` option.

An example using Symfony HttpFoundation component:

```php
/** @var Symfony\Component\HttpFoundation\Request $request */
$engine->loadExtension(new \Foil\Extensions\Uri(), ['pathinfo' => $request->getPathInfo()]);
```


## Main `uri()` method

There is only one main method for URI helpers, it is `uri()`. It returns an object on which is possible to call 3 different methods:

 - `is()`
 - `has()`
 - `match()`

All 3 helpers take as

 - 1st argument a condition to match
 - 2nd argument something to return if condition matches. Optional, default to `true`
 - 3rd argument something to return if condition doesn't match. Optional, default to `""` (empty string)

Note that all the conditions are not performed comparing full urls, but urls *path*.

E.g. if you're viewing the url `http://example.com/page` the 3 helpers will perform conditions check on `"page"`.
When viewing home page (`http://example.com`) the 3 helpers will perform conditions check on `"/"`.

What said right above is valid by default, but thanks to the `'home'` option is possible to set an home path so that all conditions check will be done assuming that path as home page.

For example, assuming the extension is loaded like so:

```php
$engine->loadExtension(new \Foil\Extensions\Uri(), ['home' => 'blog']);
```

when viewing the url `http://example.com/blog/page` the 3 helpers will perform conditions check on `"page"` and the url `http://example.com/blog` will be considered by helpers as `"/"`.


## `is()`

Most basic usage of this helper is to check identity between current url path and a path passed to it.

Assuming the url being visited is `http://example.com/a/page` (and no `'home'` option is used), in a template

```php
$this->uri()->is('a/page'); // returns true

$this->uri()->is('a/page', 'Yes'); // returns "Yes"

$this->uri()->is('another/page', 'Yes'); // returns ""

$this->uri()->is('another/page', 'Yes', 'No'); // returns "No"
```

Another way to use the helper is to set condition via a 2 items array, where the 1s item is an index (1-based) for url chunks and the 2nd item is the chunk to check for.
With *chunks* here is intended the array obtained *exploding* the url path by `/`.

For example, assuming same url as above

```php
$this->uri()->is([1, 'a']); // returns true

$this->uri()->is([2, 'page']); // returns true

$this->uri()->is([1, 'a'], 'Yes'); // returns "Yes"

$this->uri()->is([1, 'foo'], 'Yes', 'No'); // returns "No"
```

Remember that is possible to use `'home'` option to set a path to be used as *base* for all helpers checks.

For example, assuming the extension is loaded like so

```php
$engine->loadExtension(new \Foil\Extensions\Uri(), ['home' => 'blog']);
```

If url being visited is `http://example.com/blog/archive`

```php
$this->uri()->is('blog/archive'); // returns ""

$this->uri()->is('archive'); // returns true

$this->uri()->is([1, 'blog']); // returns ""

$this->uri()->is([1, 'archive']); // returns true
```

## `has()`

This is simplest helper of the extension. It accepts as condition only a string and matches the condition if current url **starts** with given string.

Assuming the url being visited is `http://example.com/a/page` (and no `'home'` option is used), in a template

```php
$this->uri()->has('a'); // returns true

$this->uri()->has('a/page', 'Yes'); // returns "Yes"

$this->uri()->has('page'); // returns ""

$this->uri()->has('a/page/deep', 'Yes', 'No'); // returns "No"
```

In the menu example above in the page this function could be used to set as selected "About Us" parent item when any of its children items is visited,
for a (probably) better readable alternative to `is()` and url chunk argument.

Below there is an use case example where this helper is used in combination with `is()` to assign selected HTML class to links in a menu:

```php
<ul>
    <li>
        <a href="<?= $this->link('/') ?>" <?= $this->uri()->is('/', 'class="selected"') ?>>Home</a>
    </li>
    <li>
        <a href="<?= $this->link('about') ?>" <?= $this->uri->has('about', 'class="selected"') ?>>About Us</a>
        <ul>
            <li>
                <a href="<?= $this->link('about/company') ?>" <?= $this->uri()->is('about/company', 'class="selected"') ?>>Company</a>
            </li>
            <li>
                <a href="<?= $this->link('about/staff') ?>" <?= $this->uri()->is('about/staff', 'class="selected"') ?>>Staff</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="<?= $this->link('contacts') ?>" <?= $this->uri()->is('contacts', 'class="selected"') ?>>Contact Us</a>
    </li>
</ul>
```

Note how "About Us" item is set as selected when any of its child items is visited
thanks to `has()`, whereas child items are selected when exact url is visited thanks to `is()`.

`link()` helper used above is documented in the *"Functions / Links Helpers"* docs section.

## `match()`

This helpers matches a given regex to current url path. When writing regex remember that:

 - there's **no** need to add regex boundary chars
 - to match home page regex must match `'/'`
 - the url path that will be checked with regex will have no trailing or leading slash (apart from the homepage which *is* a slash).
  For example if the url being visited is `http://example.com/a/page/` the matching will be performed against `'a/page'`

Assuming the url being visited is `http://example.com/a/page` (and no `'home'` option is used), in a template

```php
$this->uri()->match('^a/.+'); // returns true

$this->uri()->has('[0-9]+', 'Yes', 'No'); // returns "No"
```
