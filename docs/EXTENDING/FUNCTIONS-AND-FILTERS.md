<!--
currentMenu: "functions-filters"
currentSection: "Extending Foil"
title: "Custom Functions and Filters"
-->

# Custom Functions and Filters

# Filters

Foil filters are callbacks that receive a template variable, and return it modified.
Foil ships with some ready-made filters, that are documented in *"Data / Filters"* docs section, but is possible to write custom ones.

There is absolutely no difference on how custom and shipped filters are applied.

Filters callback receive at least one argument, the value to be filtered, and have to always return the filtered value.
However is possible to accept any number of additional arguments, but is a good idea to provide a default value for them.

As already said, filters can by any kind of PHP callbacks. To register a callback as Foil filter you need to call `registerFilter()` engine method.

A quick example:

```php
namespace MyApp;

function noVowels($str)
{
    return preg_replace('#[aeiouàèìòùáéíóúäëöü]#i', '', $str);
}

$engine->registerFilter('novowels', 'MyApp\\noVowels');
```

Using code above, in templates is possible to use `"novowels"` filter to remove any vowel from a string.

See *"Data / Filters"* docs section to know how to apply a registered filter.

# Functions

Foil functions are callbacks that can be run in templates as they are template methods, e.g. `$this->function_name()` to do... anything.

There are 2 limitations:

 - functions **can't** echo anything. If you want to register functions that output strings on a template you need to **return** string from the callback
and then echo returned value inside the template
 - when autoescape is turned on functions **can't** return objects unless the function is registered as safe (more on this below). Reason is that Foil can't autoescape objects.

Register a function is just a matter of calling `registerFunction()` engine method, passing the name of the function and the callback:


```php
namespace MyApp;

function greeting()
{
  return date('A') === 'PM' ? 'Good Evening' : 'Good Morning';
}

$engine->registerFunction('greeting', 'MyApp\\greeting');
```

Using code above, in templates is possible to use `$this->greeting()` to output a greeting.

Now take following example:

```
function hello()
{
  echo 'Lorem Ipsum';

  return '<b>Hello!</b>';
}

$engine->registerFunction('hello', 'hello');
```

Using the code above, in a template is possible to call `$this->hello()` and what is returned will be

```html
&lt;b&gt;Hello!&lt;/b&gt;
```

Note that:
- echoed string in function is just ignored
- returned string is HTML-encoded. That happen because, when autoescape is turned on (default configuration) all functions output escaped code.

To allow a function to output raw HTML content you need to register a function as safe, by passing `true` as third argument for `registerFunction()`, like so:

```php
function hello()
{
  return '<b>Hello!</b>';
}

$engine->registerFunction('hello', 'hello', true);
```

## Why Foil Functions?

Once Foil templates are just PHP files, is always possible just call any PHP function, so why register a callback as Foil function and not just call it *as is* inside a template?

There are a few reasons:

- Easy naming. Custom PHP functions can be registered in global namespace, ending up in functions names like `do_something_in_template()`
or under a namespace, something like `MyApp\Template\Tools\doSomething()`.
In both cases call them in templates is not very comfortable, on the contrary, something like `$this->something()` is far easier to type and makes templates cleaner.
- Autoescape. As better explained above in this page, when autoescape is turned on (default) Foil functions are not allowed to return HTML tags, unless specifically required, improving security.
- By registering class methods as Foil functions is possible make use of features provided by any object (even from external libraries) without having to instantiate classes in templates or pass object instances to templates.
Moreover, is possible to change internal implementation (e.g. replace an external library with another) without having to change templates.
- The fact that Foil functions can't echo anything, prevents unwanted side effects and assure compatibility with Foil.

Additional benefits (e.g. access Foil internal objects) can be obtained when functions are registered via extensions, see *"Extending Foil / Custom Extensions"* for details.
