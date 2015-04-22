<!--
currentMenu: "datahelpers"
currentSection: "Data"
title: "Data Helpers"
-->

# Data Helpers

Foil comes with a set of helpers that facilitate the handling of template data.

They are:

- `raw()`
- `e()`
- `eJs()`, `eCss()`, `eAttr()`
- `d()`
- `v()`
- `a()`
- `ifnot()`

All of them share **same signature** and support:

- dot syntax to access variables in nested arrays
- default value for non existent variables
- filters

## Helpers Signature

All helpers listed above share same signature, below there is an example using `v()`:

```php
/**
 * @param string       $var     Variable name, usually is the key in the data array passed to render()
 * @param mixed        $default Returned when requested variable is not defined
 * @param string|array $filter  Filter(s) to apply to retrieved variable (or to default)
 *
 * @return mixed
 */
function v($var, $default = '', $filter = null);
```

# Helpers Functions

## `raw()`

This helper allows to output unescaped strings when autoescape is turned on.

## `e()`

This helper is used to get and escape a variable when autoescape is turned off.

```php
// assuming $this->a_var_with_html is '<strong>I am "bold"</strong>'
echo $this->e('a_var_with_html'); // echo &lt;strong&gt;I am &quot;bold&quot;&lt;/strong&gt;
```

Note that the encode routine applied is the same Foil uses when autoescape is turned on, that is capable to encode strings and also arrays containing strings. It is based on [AuraPhp/HTML](https://github.com/auraphp/Aura.Html) library.

For example:

```php
/*
Assuming $this->an_array is equal to:
[
    'one' => '<b>A</b>'
    'two' => [
      'a'     => '<b>A</b>',
      'three' => [
            'a' => '<b>A</b>',
            'b' => '<b>B</b>',
            'c' => '<b>C</b>',
      ]
    ]
]
*/

$this->e('an_array');

/*
Returns:
[
    'one' => '&lt;b&gt;A&lt;/b&gt'
    'two' => [
        'a'     => '&lt;b&gt;A&lt;/b&gt',
        'three' => [
              'a' => '&lt;b&gt;A&lt;/b&gt',
              'b' => '&lt;b&gt;B&lt;/b&gt',
              'c' => '&lt;b&gt;B&lt;/b&gt',
        ]
    ]
]
*/
```

This helper, just like the others, accepts a variable *name*, but if you need to escape a *value*, you can use the `escape()` function (or its alias `ee()`).

For example, the following three lines below are equivalent:

```php
echo $this->e('var_name');

echo $this->escape($this->raw('var_name'));

echo $this->ee($this->raw('var_name'));
```

## `eJs()`, `eCss()`, `eAttr()`

`e()` helper uses [AuraPhp/HTML](https://github.com/auraphp/Aura.Html) library to escaping data. That library allows to use a different escaping "targets".

In fact, it is possible to properly escape data to be placed inside JavaScript scripts, inside CSS or inside HTML attributes.

`eJs()`, `eCss()` and `eAttr()` can be used to escape data (strings or array containing strings) that have to be used, respectively, for JavaScript, CSS or HTML attributes.

When using the helpers `escape()` (or its alias (`ee()`) is possible to use a second argument ot use an alternative escape target, e.g.:

```php
$this->escape($data, 'js');
```

## `v()`

"v" stands for "variable" and, as you can guess, returns a template variable.

When autoescape is turned on `v()` acts like `e()` otherwise acts as `raw()`.

## `a()`

"a" stands for "array" and it acts similar to `v()` but ensures that the returned value is an array and assumes an empty array as default.

It is useful to be used in situations where an array is required, e. g. to be used in `foreach`.

```php
foreach($this->a('a_variable') as $item) {
  echo $item;
}
```
Note that if the variable is not defined or empty code above just echo nothing, without the need to check if variable is set and has proper type.

Internally this helper uses the function `Foil\arraize()` to assure the value you get is an array.
Please read *"Data / Arraization"* docs page to understand how it works (at the page end of that page there are notes regarding this helper).

## `araw()`

Similar to `a()` but doesn't escape retrieved array even if autoescape is turned on.

## `d()`

This is the exact counter part of `e()`: it decode HTML entities. It can be handy when there are a lot of data, e.g. a big array, and only one variable contain trusted HTML content.

Instead of retrieving the array using `raw()` and then escape one by one all the values, is far easier retrieve it escaped with `v()` (or `a()`) and then
*decode* the only var that contain HTML.

You should use this function only when the variable is trusted.

This helper, just like the others, accepts a variable *name*, but if you need to decode a *value*, you can use the `decode()` function (or its alias `dd()`).

For example, the following three lines below are equivalent:

```php
echo $this->d('var_name');

echo $this->decode($this->e('var_name'));

echo $this->decode($this->e('var_name'));
```

## `ifnot()`

This helper has same signature of the others, and just like the others returns the default value if the variable is not defined,
however, it also outputs default even if the variable is defined but empty. On the contrary if the variable is defined and not empty returns nothing.

Below there is an example of how this helper can be used in combination with `a()` helper to reproduce something that in [Twig is done via `for` / `else`](http://twig.sensiolabs.org/doc/tags/for.html#the-else-clause):

```php
<ul>
    <?php foreach($this->a('users') as $user) : ?>
        <li><?= $user['username'] ?></li>
    <?php endforeach ?>

    <?= $this->ifnot('users', '<li><em>no user found</em></li>') ?>
</ul>
```

Continue reading to the ***"Default"*** section further in this page to see how to easily set multiline HTML content as default.

# Helpers Features

## Dot Syntax

All helpers support dot syntax to access variables in nested arrays.

```php
$data = [
  'a' => [
    'pretty' => [
      'deep' => [
        'var' => 'Hello World'
      ]
    ]
  ]
];

$engine->render('template', $data);
```

Assuming code above, in the `template.php` file is possible to echo "Hello World" with:

```php
<?= $this->v('a.pretty.deep.var') ?>
```

Nice thing is there is no need to recursively check for a key existence in a deep array, because if any of the keys doesn't exist the helper just returns an empty string without throwing any error.

```php
<?= $this->v('a.pretty.inexistent.var') // echo nothing ?>
```

Without this helper, the necessary code to obtain the same result of the single line above would be something like:

```php
$a = isset($this->a) ? $this->a : null;
if (
  is_array($a)
  && isset($a['pretty'])
  && isset($a['pretty']['deep'])
  && is_array($a['pretty']['deep'])
  && isset($a['pretty']['deep']['var'])
) {
  echo $a['pretty']['deep']['var'];
}
```

## Default

All helpers support as 2nd argument a default value to be returned if required variable is not defined.

```php
<?= $this->v('a.non.defined.variable', 'Default!') // echo "Default!" ?>
```

### Closures as Default

There is a special case: when default is an [**anonymous function**](http://php.net/manual/en/functions.anonymous.php).

In that case, the anonymous function is ran and the result is returned.
Moreover, if the anonymous function returns nothing, but echoes something, whatever it echoes is returned.

This feature can be used to easily output multiline HTML markup as default:

```php
<?= $this->v('a.non.defined.variable', function() { ?>
    <div>
        <h1>Sorry!</h1>
        <p>The variable is not defined...</p>
    </div>
<?php }) ?>
```

### Partials as Default

You might think that partials, returned via `insert()`, could be a concise way to write big and reusable blocks of HTML content:

```php
<?= $this->v('a.non.defined.variable', $this->insert('default-partial')) ?>
```
However there's an issue: when autoescape is turned on, default content is escaped too, so all the content of the template is HTML encoded.

The easiest way to overcome this issue is to use the [ternary operator](http://php.net/manual/en/language.operators.comparison.php#language.operators.comparison.ternary) instead of using `insert()` for 2nd argument:

```php
<?= $this->v('a.non.defined.variable') ?: $this->insert('default-partial') ?>
```

## Filters

Filters are callbacks that modify a variable value. Foil comes with several ready-made filters, but is also possible to register custom ones.

All helpers support as third argument one or more filters to be applied to a variable (or to default value, if variable is not defined).

One of the filters shipped with Foil is `'escape'` that act in the same way of `e()` helper.

The two lines in code below are equivalent.

```php
$this->raw('a.var', '', 'escape');

$this->e('a.var', '');
```

See *"Data / Filters"* docs section to know all filters shipped with Foil, and *"Extending Foil"* to know more about custom filters.


## Filters "Waterfall"

In a single line is possible to apply more filters to a variable, where each filter is applied on the result of the previous.

To apply more filters is possible to pass all filter names as an array or as a pipe-separated string:

```php
/* Assuming "var_name" variable is equal to:

[
    'items' => [
        'one' => '<b>One</b>',
        'two' => '<b>Two</b>'
    ]
]
*/

$this->raw('var_name.items', '', 'escape|first');       // &lt;b&gtOne;&lt;/b&gt;
$this->raw('var_name.items', '', ['escape', 'first']);  // &lt;b&gtOne;&lt;/b&gt;
```

Code above makes use of `'first'` filter that is another of the filters shipped with Foil and returns first element of an array.

All helpers support another way to apply one or more filters to a variable.
Filters can also be set using a pipe-separated list of filters after variable name in helper first argument:

```php
$this->raw('var_name.items|escape|first');              // &lt;b&gtOne;&lt;/b&gt;
$this->raw('var_name.items', '', 'escape|first');       // &lt;b&gtOne;&lt;/b&gt;
$this->raw('var_name.items', '', ['escape', 'first']);  // &lt;b&gtOne;&lt;/b&gt;
```

Looking at the code right above, you can realize that none of the three methods to apply filters allows to pass additional arguments to filter callbacks, and the only argument that filter callbacks receives is the variable to be filtered.

If your filter callbacks need additional arguments, you have to use the `filter()` template function.

You can find documentation for that function and for the related helper `f()` in the *"Data / Filters"* docs section.
