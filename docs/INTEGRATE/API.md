<!--
currentMenu: "apifunctions"
currentSection: "Integrate Foil"
title: "API"
-->

# API

## Warning

Foil versions older than 0.6 used to have more API functions.

With v0.6 number of functions decreased and now are available the only available functions are listed below.

Moreover, in older versions, there was available an `API` object that worked as a *proxy* to API functions.
That object **is no more available** starting from version 0.6.

## Available functions

All the API functions are available under the `Foil` namespace.

The available functions are:

 - `Foil\engine()`
 - `Foil\render()`
 - `Foil\arraize()`

## `Foil\engine()`

```php
    /**
     * @param  array        $options   Options: autoescape, default and allowed extensions, folders...
     * @param  array        $providers Custom service provider classes
     * @return \Foil\Engine
     */
    function engine(array $options = [], array $providers = [])
```

Available since very first release, this function can be used to obtain an instance of Foil `Engine`
that can be used to render templates and access to most of Foil features.

`$options` argument is used to configure Foil behavior, see "Getting Started" section to know more.

In versions older than 0.6, when called more than once, this function used to return always the same instance of Foil Engine class.

Starting from v0.6, it always return a "fresh" instance of Engine.

Also note that in v0.6+, there's another method to obtain an instance of Foil Engine. It is the `engine()` method of the `Foil\Foil` class, whose instance
can be obtained via `Foil\Foil::boot()` static method. See *"Getting Started / Engine"* to know more on the topic.


## `Foil\render()`

```php
    /**
     * @param  string $path    Full path or just name (requires folders option) for the template
     * @param  array  $data    Template context
     * @param  array  $options Options for the engine
     * @param  array  $providers
     * @return string
     */
    function render($path, array $data = [], array $options = [], array $providers = [])
```

Introduced in v0.6, this function can be used to quickly render a template with a given set of data.

It very similar to obtain an instance of the engine via `engine()` function, and then call `render()` method on it.

First argument, `$path` may be the full absolute path of a template file, or just a template file name, in this latter case it requires a set of folders
to be set using `$options`.


## `Foil\arraize()`

```php
    /**
     * @param  array $data
     * @param  bool  $escape
     * @param  array $transformers
     * @param  bool  $toString
     * @return mixed
     */
    function arraize($data = [], $escape = false, array $transformers = [], $toString = false)
```

This function allow to recursively convert kind of data to an array.

It is a powerful tool, and there is an entire doc page that explain how it work and how to use it.

See "Data / Arraization".