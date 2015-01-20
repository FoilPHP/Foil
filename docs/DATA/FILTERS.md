<!--
currentMenu: "datafilters"
currentSection: "Data"
title: "Data Filters"
-->

# Filters

Filters are callbacks that modify a given value and return the modified value.

Foils ships with several ready-made filters, but is possible to easily add custom filters.
See *"Extending Foil"* docs section to know more about custom filters.

## Foil Filters

Filters that comes with Foil are:

 - `'e'`, html-encodes a string, or any string contained in an array
 - `'first'`, returns first item of an array or first char of a string
 - `'last'`, returns last item of an array or last char of a string
 - `'chunk'`, chunks an array by returning an array of arrays with the given number of items. It is also possible to provide a value to fill missing items. It works very similar to [`batch` Twig filter](http://twig.sensiolabs.org/doc/filters/batch.html).


## Apply Filters

There are three ways to apply filters in Foil, and all supports filter "waterfall", that is apply more filters at once, where next filter in the list receives result of the previous.

Filters can be applied with:

 - `filter()` template method
 - `f()` template helper
 - passing filter as 3rd argument for any of the data helpers. See *"Data / Helpers"* for more info.

Both `filter()` and `f()` accept as first argument one or more filters, as array or as pipe-separated list.

Second arguments for both functions is the variable to filter, but `filter()` accepts the variable *value* to filter, where `f()` accepts the variable *name*.

The two lines the template code below are equivalent.

```php
$this->filter('e|first', $this->foo);

$this->f('e|first', 'foo');
```

`f()` helper supports dot syntax to access variables in nested arrays.

Again, the two lines the template code below are equivalent.

```php
$this->filter('e|first', $this->raw('a.pretty.deep.var'));

$this->f('e|first', 'a.pretty.deep.var');
```

See *"Data / Helpers"* to know more about Foil dot syntax and `raw()` helper used in code above.

## Examples

Below there are some examples of Foil filters usage.

The following Foil engine bootstrap code is used as context for all examples:

```php
$data = [
  'some'   => [
      'pretty' => [
          'deep' => [
              'vars' => [
                  'lorem' => '<b>Lorem</b>',
                  'ipsum' => '<b>Ipsum</b>',
                  'dolor' => '<b>Dolor</b>',
                  'sit'   => '<b>Sit</b>',
                  'amet'  => '<b>Amet</b>',
              ]
          ]
      ]
  ]
];

$engine = Foil\engine([
  'folders'    => 'path/to/templates',
  'autoescape' => false
]);

echo $engine->render('template', $data);
```

## `"first"`

```php
$this->filter('first', $this->v('some.pretty.deep.vars')); // '<b>Lorem</b>'

$this->f('first', 'some.pretty.deep.vars'); // '<b>Lorem</b>'

$this->filter('first|e', $this->v('some.pretty.deep.vars')); // '&lt;b&gt;Lorem&lt;/b&gt;'

$this->f(['first', 'e'], 'some.pretty.deep.vars'); // // '&lt;b&gt;Lorem&lt;/b&gt;'
```

## `"last"`

```php
$this->filter('last', $this->v('some.pretty.deep.vars')); // '<b>Amet</b>'

$this->f('last', 'some.pretty.deep.vars'); // '<b>Amet</b>'

$this->filter('last|e', $this->v('some.pretty.deep.vars')); // '&lt;b&gt;Amet&lt;/b&gt;'

$this->f(['last', 'e'], 'some.pretty.deep.vars'); // // '&lt;b&gt;Amet&lt;/b&gt;'
```

## `"chunk"`

```php
echo '<table>';
foreach ($this->f('chunk', 'some.pretty.deep.vars', [2]) as $row) {
  echo '<tr>';
  foreach($row as $column) {
    printf('<td>%s</td>', $column);
  }
  echo '</tr>';
}
echo '</table>';

/*
will echo:

<table>
    <tr>
        <td><b>Lorem</b></td>
        <td><b>Ipsum</b></td>
    </tr>
    <tr>
        <td><b>Dolor</b></td>
        <td><b>Sit</b></td>
    </tr>
    <tr>
        <td><b>Amet</b></td>
    </tr>
</table>
*/
```

`"chunk"` also accepts an additional argument to be used as *filler* for missing items, e.g. if in code above I'd used:

```php
foreach ($this->f('chunk', 'some.pretty.deep.vars', [2, '<b>Fill With Me</b>']) as $row) {
```

the output would be:

```html
<table>
    <tr>
        <td><b>Lorem</b></td>
        <td><b>Ipsum</b></td>
    </tr>
    <tr>
        <td><b>Dolor</b></td>
        <td><b>Sit</b></td>
    </tr>
    <tr>
        <td><b>Amet</b></td>
        <td><b>Fill With Me</b></td>
    </tr>
</table>
```


## Filters Additional Arguments

As demonstrated in the `"chunk"` example above, to pass arguments to filter callbacks both `filter()` and `f()` functions accept a third argument that is the array of additional arguments to pass to filter callback.

`"chunk"` is the only filter that comes with Foil that accepts additional arguments, but any of the custom filters you may write can accept additional arguments too.

When in "waterfall" mode, third argument for `f()` and `filter()` must be an array of arrays, that must have as many items as the filter count, in a way that every filter has its own array of arguments.

Please note that in "waterfall" mode if even only one filter needs additional arguments you have to pass additional arguments array for all filters, even for ones doesn't need them, in which case you'll pass an empty array.

Let's assume you created two custom filters: `"filter_one"` that doesn't accepts additional arguments, and `"filter_two"` that accepts 2 additional arguments.

You can use them in "waterfall" mode, with code like the following:

```php
$this->f(
 'chunk|escape|filter_one|filter_two',
 'some.pretty.deep.vars',
 [
     [2, 'Filler'], // additional args for "chunk"
     [],            // additional args for "escape"
     [],            // additional args for "filter_one"
     ['foo', 'bar'] // additional args for "filter_two"
 ]
);
```


## Default Value For `f()`

`f()` accepts as the value to filter a variable name, that may or may not be defined.
By default, `f()` will consider undefined variables equal to an empty string and **will pass that empty string to filters**.
It means that `f()` may return something even if the variable name passed to it is not defined.

To avoid that you may use the `filter()` function instead, after checking the value to filter, e.g.

```php
$value = $this->v('some.pretty.deep.vars');
echo $value ? $this->filter('custom_filter', $value) : '';
```

`f()` also accepts a 4th argument, that is the value to be used as default if the required variable is not defined

```php
$this->f(
  'first|escape|custom_filter',
  'some.pretty.deep.vars',
  null,
  'I am a default'
);
```

Please note that if you don't need to pass additional filter arguments, but you need a default value, like in the example above, 3rd argument for `f()` must be `null`, any other value will cause an error.
