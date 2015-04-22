<!--
currentMenu: "api"
currentSection: "Integrate Foil"
title: "API Functions"
-->

# API Functions

### Note On Function Names

API functions can be called as namespaced functions, e.g. `Foil\engine()` or as methods of Foil API object.

Function names with more than one word are written using snake_case and you can call them in that way as API object methods,
but for consistence with PSR-1 coding style you can also call them using camelCase.

For example, the function `Foil\render_template()` can also be ran with `$api->renderTemplate()`.

---

# `foil()`

```php
/**
 * @param  string|void $which            Service id
 * @param  array       $options          Engine options
 * @param  array       $custom_providers Custom service provider classes
 *
 * @return mixed       The container or the service whose id has been passed in $which
 */

function foil($which = null, array $options = [], array $custom_providers = [])
```

You should very rarely (close to never) use this function directly. In fact, it is used by all the functions to access
Foil DI container, that is a [Pimple](http://pimple.sensiolabs.org/) instance or any service registered in it.

The only case in which you need to use this function is when you want to add additional service providers to be registered
in Foil container.

That can be done by passing to the function an array of service provider class names as 3rd argument.
Note that in that way you can also override default Foil service providers, but it implies you know what you are doing or sky will fall on your head.

Only in such case, you can get a Foil engine instance with `$engine = Foil\foil('engine', $options, $custom_providers)` instead of using the
`engine()` function.

---

# `engine()`

```php
/**
 * @param  array        $options Options: autoescape, default extension, folders...
 *
 * @return \Foil\Engine
 */

function engine(array $options = [])
```

This function is the preferred way to obtain an instance of Foil engine that can be used to render templates and access to most of Foil features.

`$options` argument is used to configure Foil behavior, see *"Getting Started"* section to know more.

When called more than once the function returns always the same instance of the engine. Also note that you need to pass engine options only first time the function is called.

---

# `render_template()`

```php
/**
 * @param  string $path    Full path for the template
 * @param  array  $data    Template context
 * @param  array  $options Options for the engine
 *
 * @return string
 */

function render_template($path, array $data = [], array $options = [])
```

This is an alternative to call `render()` method on Foil engine instance, but there is a difference:
this function needs a template full file path, whereas `render()` needs just template name.

Using this function is possible to quickly render a template and you don't need to set any template folder for the engine.

If in your application you have a custom method to retrieve template file paths, this function will be handy.

It worth noting that `renderTemplate()` can be called on a Foil engine instance too.

---

# `option()`

```php
/**
 * @param  string $which Option name
 *
 * @return mixed
 */

function option($which = null)
```

This function can be used to retrieve any option that was passed when Foil engine was instantiated.

It is pretty useful in API-aware extensions (see *"Extending Foil / Custom Extensions"*) to create configurable custom functions and filters.

---

# `add_context()`

```php
/**
 * @param array   $data     Data to be passed to matching templates
 * @param string  $needle   String to compare template name to
 * @param boolean $is_regex If true template name will be compared using $needle as a regex
 */

function add_context(array $data, $needle, $is_regex = false)
```

This function is equivalent to `$engine->useContext()` method when used to pass data to groups of templates based on a string search (optionally regex) against their names.

See *"Data / Context API"* for more information.

---

# `add_global_context()`

```php
/**
 * @param array $data Data to be passed to **all** templates
 */

function add_global_context(array $data)
```

Adds some data to all templates. Is equivalent to `$engine->useData()`.

See *"Data / Context API"* for more information.

---

# `add_context_using()`

```php
/**
 * @param ContextInterface $context Context class to add
 */

function add_context_using(Foil\Contracts\ContextInterface $context)
```

This function is equivalent to `$engine->useContext()` when used to pass data to groups of templates using a **custom context class**.

See *"Data / Context API"* for more information.

---

# `run()`

```php
/**
 * @param string $function Function name
 *
 * @return mixed
 */

function run($function)
```

Custom functions registered to extends Foil features can be ran inside template files as methods of template object (accessed via `$this`).

This function allows to run any registered custom function outside template files.

Additional arguments that the registered function might take can be passed as additional arguments to this function, e.g. `Foil\run('func_name', $arg1, $arg2)`

---

# `entities()`

```php
/**
 * @param mixed $data Data to encode
 * @param string $strategy Target for escaping, can be 'html', 'js', 'css', 'attr'
 * @param string $encoding Character encoding used for escaping, default 'utf-8'
 *
 * @return mixed
 */

function entities($data, $strategy = 'html', $encoding = 'utf-8')
```

This function allows to perform on any data, and outside of template files, the same routine Foil uses to escape content with automatic escape or with escape helpers.

This function is capable to HTML-encode strings and arrays (even deeply nested) that contain strings. Any other data type will be returned unchanged.

The routine applied is based on [AuraPhp/HTML](https://github.com/auraphp/Aura.Html) library.

By default the given data is HTML-encoded using UTF-8, is possible to change the behavior using `$strategy` and `$encoding` arguments.

 - with `$strategy = 'js'` is possible to escape strings to be safely placed inside JavaScript scripts
 - with `$strategy = 'css'` is possible to escape strings to be safely placed inside CSS code
 - with `$strategy = 'attr'` is possible to escape strings to be safely inside HTML attributes. This is the only strategy that can be used passing an array,
   where array keys are attribute names, e.g. `class` and array values are attribute values.

---

# `decode()`

```php
/**
* @param mixed  $data Data to HTML-decode
* @param string $encoding Character encoding used for escaping, default 'utf-8'
*
* @return mixed
*/

function decode($data, $encoding = 'utf-8')
```

This function is the counterpart for `entities()`. It is capable to HTML-decode strings and arrays (even deeply nested) that contain strings.
Any other data type will be returned unchanged.

This is 100% affordable ony if data were HTML encoded using same character encoding of decoding and, in case data comes from Foil `entities()` function (see above)
if the strategy used was `'html'`. This is always true if data was escaped by Foil autoescape routine.

---

# `arraize()`

```php
/**
* @param  mixed $data         Data to convert
* @param  bool  $escape       Should strings in data be HTML-encoded?
* @param  array $transformers Transformers: full qualified class names, objects or callables
* @param  bool  $tostring     Should all scalar items be casted to strings?
* @return array
*/

function arraize($data = [], $escape = false, array $transformers = [], $tostring = false)
```

This function allow to recursively convert to array any kind of data. It is a powerful tool, and there is an entire doc page that explain how it work and how to use it.

See *"Data / Arraization"*.

---

# `fire()`

```php
/**
 * @param string $event Event name
 */

function fire($event)
```

Internally Foil uses an events emit / subscribe system to implements most of its features.

Foil uses the awesome [Evenement](https://github.com/igorw/evenement) package to implement it.

This function allows to emit a custom event, and to do that you only need to pass the event name as 1st argument.

Additional arguments passed to function will be passed in same order to subscribing callbacks.

This function can be used in API-aware extensions to emit custom events that can be subscribed by yourself or by 3rd parties.

---

# `on()`

```php
/**
 * @param string   $event    Event name
 * @param callable $callback Callback called when event is emitted
 * @param boolean  $once     Should the callback run only once?
 */

function on($event, callable $callback, $once = false)
```

This function is part of the events emit / subscribe system used by Foil and allows to subscribe an event using a callback.

This function can be used in API-aware extensions to subscribe events emitted by Foil (there are quite a lot), or by code you or a 3rd party wrote.

---
