<!--
currentMenu: "htmlhelpers"
currentSection: "Blocks & Helpers"
title: "HTML & Form Helpers"
-->

# HTML & Form Helpers

Foil (v0.4+) integrates [Aura.HTML](https://github.com/auraphp/Aura.Html) library that is used for **escape** (and autoescape) routine.

Moreover, Aura.HTML **[html](https://github.com/auraphp/Aura.Html/blob/2.x/README-HELPERS.md) and 
[form](https://github.com/auraphp/Aura.Html/blob/2.x/README-FORMS.md) helpers** are supported via a shipped extension.

## `html()`

By default, Foil has just one helper that works as interface for all Aura.HTML helpers. It is `html()`.

The supported helpers are:

 - anchor / anchorRaw
 - base
 - img
 - label
 - links
 - metas
 - ol
 - scripts
 - styles
 - tag
 - title
 - ul
 
Please refers to [Aura.HTML docs](https://github.com/auraphp/Aura.Html/blob/2.x/README-HELPERS.md) to
know more.
 
The first argument for `html()` must be the helper name (one from the list above), all other arguments required by Aura.HTML helpers are supported.

For example, to render an `<img>` tag is possible to do:

```php
$this->html('img', '/images/hello.jpg', ['id' => 'image-id']);
```

That will render:

```html
<img src="/images/hello.jpg" alt="hello" id="image-id">
```

The example comes from [Aura.HTML docs](https://github.com/auraphp/Aura.Html/blob/2.x/README-HELPERS.md#img),
refers to that docs page to know how to similarly use all the supported helpers.


## Short HTML Helpers

Setting an Engine option, `"html_tags_functions"`, to `true`, Foil will register additional HTML helpers,
that allow to call the supported Aura.HTML helpers straight on template instance (`$this`).

For example, to print same `<img>` tag as above:

```php
$engine = Foil\engine([
  'folders'             => ['path/to/templates'],
  'html_tags_functions' => true
]);

echo $engine->render('example');

// and inside path/to/templates/example.php
<?= $this->img('/images/hello.jpg', ['id' => 'image-id']) ?>
```

All the other tags are supported in same way.

### Note for `a()` helper

Aura.HTML has the helper `a()` to print an `<a>` HTML tag. However, `a()` is already a Foil helper,
used to force a template variable to be an array (see *Data / Helpers*), for this reason to print an
anchor tag when `'html_tags_functions'` option is true use `anchor()` function, that is supported by
Aura.HTML as well.


## Form Helpers

Aura.HTML has some [form helpers](https://github.com/auraphp/Aura.Html/blob/2.x/README-FORMS.md#html-5-input-elements) that
can be used to render HTML5 form inputs.

They are:

 - button
 - checkbox
 - color
 - date
 - datetime
 - datetime-local
 - email
 - file
 - hidden
 - image
 - month
 - number
 - password
 - radio
 - range
 - reset
 - search
 - select (including options)
 - submit
 - tel
 - text
 - textarea
 - time
 - url
 - week

Foil supports all of them via `html()` helper. For example:

```php
$this->html('submit', ['name' => 'foo','value' => 'bar']);
```

That will render:

```html
<input type="submit" name="foo" value="bar" />
```

So the first argument for `html()` must be the input name, the second argument must be an array of all
 the arguments supported by Aura.HTML.

Note that Aura.HTML requires the argument `'type'` to be set to the input type ('text', 'submit'...) 
that is not required by  Foil `html()` helper because input type is passed as first argument.

An alternative way to render form inputs (more similar to Aura.HTML syntax) is to use the generic `'input'`
as first argument for `html()`, then pass the input type as part of arguments array.

For example:

```php
$this->html('input', ['type' => 'submit', 'name' => 'foo','value' => 'bar']);
```

## `input()` Helper

When `'html_tags_functions'` option is `true` (see "Short HTML Helpers" above) form input names are **not** available
as template functions, because something like `$this->submit()` or `$this->text()` is hardly recognized as something
that prints a form input.

However, activating `'html_tags_functions'` options is possible to use `input()` helper. For example:

```php
$this->input(['type' => 'submit', 'name' => 'foo','value' => 'bar']);
```


