<!--
currentMenu: "engine"
currentSection: "Getting Started"
title: "Engine"
-->

# Engine

There are very few components in Foil you should interact with. The most important is the Foil **Engine**.

## Obtaining an Engine instance

An instance of that class can be obtained using:

 - **`Foil\engine()`** function..
 - **`Foil\Foil::boot()`** method. It returns an instance of `Foil\Foil` class. That class has a method `engine()` that returns an instance of the engine.

You need to pass to both an array of arguments that let you configure how Foil engine will work.

The two methods works in same way, the `Foil\Foil::boot()` method was introduced in version `v0.6`.

### With Engine Function

```php
$engine = Foil\engine([
  'folders' => ['path/to/templates']
]);
```

### With `boot()` Method (since v0.6)

```php
$foil = Foil\Foil::boot([
    'folders' => ['path/to/templates']
]);
$engine = $foil->engine();
```

## Render a template file with some data

Having an instance of the engine, render a template with some data is just a matter of:

```
echo $engine->render('a-template', $some_data);
```

The code above is a very simple (and working) example on how to instantiate Foil engine and use it to render a template with some data.

- `render()` method here takes just template name (no file extension, no folder): engine will search for the template in registered template folders. Foil engine will also add default extension, that is `.php`, to file name. This behavior can be customized using engine options.

- `$some_data` must be an associative array. Array keys will be variable names in the template. There are powerful tools in Foil to interact with template data inside template file. Moreover, directly passing data to `render()` is not the only way to add context for templates, Foil comes with a context API that lets you pre-assign data to templates under specific conditions.



## Warning

The function `engine()` in versions older than `v0.6` used to return the *same* instance of the engine when called more times during same request.

This was changed in version `v0.6`, and now every time `Foil\engine()` is called it returns a *"fresh"* instance of the engine, that has nothing
shared with any previously got instance.

Create an instance of the engine may consume some resources, so re-using the same instance can be a good idea, however, that task is now left to implementers that are free to do that as they prefer.
