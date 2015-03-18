<!--
currentMenu: "retrievedata"
currentSection: "Data"
title: "Retrieve Data Inside Templates"
-->

# Retrieve Data Inside Templates

There are two main ways to retrieve data inside a template

- get data as template instance properties (accessed via `$this`)
- use helpers

The latter is explained in the *"Data / Helpers"* docs section.

First method is easily explained via an example.

Assuming a Foil template rendering is launched like so:

```php
Foil\engine(['folders' => 'path/to/templates'])
  ->render('template-example', ['foo' => 'Bar', 'bar' => 'Baz']);
```

inside `template-example.php`

```php
echo $this->foo;
```

outputs "Bar".

It worth noting that if a variable is not defined, no notice or warning is thrown, saving you from check for variable existence.

E.g. in previous example

```php
echo $this->a_non_defined_var;
```

will just echo nothing.

## Automatic Escaping

By default, template data is automatically escaped, i.e. it is HTML-encoded with support for UTF-8.

Assuming a Foil template rendering is launched like so:

```php
Foil\engine(['folders' => 'path/to/templates'])
  ->render('template-example', ['bold' => '<strong>I am "bold"</strong>']);
```

inside the template `$this->bold` will be equal to

```html
&lt;strong&gt;I am &quot;bold&quot;&lt;/strong&gt;
```

## Automatic Escaping Gotchas

Given that escape data that may possibly come from user input (even when saved in a database) **is really important** to avoid vulnerability to XSS attacks,
you need to be sure **all** strings you output are escaped. For the purpose you need to make a choice:

 - Turn automatic escape OFF, and remember to use escape helpers to escape **anything** before output to HTML
 - Leave automatic escape ON, and be sure to carefully read following paragraphs to understand Foil automatic escape limitations and practices to overcome them

> **Foil is capable to automatically escape only strings and arrays (even deeply nested) that contain strings.**

If you use objects as template context and use that object vars or methods to output strings, those strings **can't** be automatically escaped by Foil.

So, if you want to use Foil automatic escape you have 2 options to choose from:

 - be sure that any object whose methods or properties are used in template outputs escaped content
 - only pass arrays or strings as template data

The second option may appear limiting, because a lot of times template data comes from persistent data layer (mostly database) libraries that return objects.

However, convert that collections of objects into array before passing them to a Foil template is all but a bad idea, because:

 - it will make you able to use automatic escape with no worries
 - it will make printing data a lot easier, thanks to the dot syntax support that Foil helpers provide for nested arrays (see *"Data / Helpers"* to know more on the topic)
 - it will decouple your templates from data layer objects, improving maintainability and general quality of your code

## Introducing *Arraization*

Foil has a simple, but pretty powerful tool to converts any kind of data into an array.

(Of course, you are not forced to use it, in fact you can use any method or any library you like for the purpose.)

The *Foil way* is pretty simple but powerful, it is accessed via a single function: **`arraize()`**.

It does the conversion is done in very flexible way that may work out of the box with a lot of libraries. (Just as example it will work out of the box with [Eloquent](http://laravel.com/docs/4.2/eloquent) models).

I want to remark that to use this system is absolutely **not required** to use Foil.

For this reason, and also to make it more readable, full explanation on how `arraize()` works is in a separate docs section, if you're interested see *"Data / Arraization"*.

## Double Quote Around HTML Attributes

No matter if you escape a variable thanks to automatic escaping or using escape helpers, when you output something inside HTML attributes is **important** that you wrap it using **double quotes**:

```html
<!-- double quotes: GOOD -->
<div id="<?= $this->autoescaped_string ?>">

<!-- double quotes: GOOD -->
<div id="<?= $this->e('a_string') ?>">

<!-- single quotes: BAD -->
<div id='<?= $this->e("a_string") ?>'>

<!-- no quotes: BAD -->
<div id=<?= $this->escape($a_string) ?>>
```

`e()` and `escape()` helpers are documented in *"Data/ Helpers"* docs section.


## Turn Automatic Escape Off

Automatic escape can be turned off by setting `"autoescape"` engine option to false:

```php
$engine = Foil\engine([
  'folders'    => 'path/to/templates',
  'autoescape' => false                // turn autoescape off
])
```

## Raw Output

To output HTML content when autoescape is turned on, is possible to use `raw()` template function

```php
$this->raw('some_html_content');
```

Code above will return the string saved in `$this->some_html_content` with non-escaped HTML tags.

Just **be sure to sanitize any content possibly coming from users** when using this function.

`raw()` is one of the Foil template helpers, there are some and all are documented in *"Data / Helpers"* section.


## Strict Variables

As of 0.3 Foil supports `strict_variables` engine option. By setting it is possible to throw an exception or a notice when a non-defined variable is used.
That might be useful for debug because default behavior that silently ignores undefined variables can make hard to find code issues.

Option supported values are:

 - `false`: default behavior, undefined variables are ignored
 - `true`: if an undefined variable is required, Foil throws an exception
 - `'notice'` if an undefined variable is required, Foil triggers a notice (execution is not halted)
 
Example:
 
```php
$engine = Foil\engine([
  'strict_variables' => true
]);

echo $engine->render('a-template', $some_data);
