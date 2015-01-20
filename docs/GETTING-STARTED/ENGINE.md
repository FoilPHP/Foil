<!--
currentMenu: "engine"
currentSection: "Getting Started"
title: "Engine"
-->

# Engine

There are very few components in Foil you should interact with.

The most important is the Foil **Engine**. An instance of that class can be obtained using **`Foil\engine()`** function.

You need to pass (only first time you call it) an array of arguments that let you configure how Foil engine will work.

```php
$engine = Foil\engine([
  'folders' => ['path/to/templates']
]);

$engine->render('a-template', $some_data);
```

The code above is a very simple (and working) example on how to instantiate Foil engine and use it to render a template with some data.

Please note:

- `render()` method here takes just template name (no file extension, no folder): engine will search for the template in registered template folders. Foil engine will also add default extension, that is `.php`, to file name. This behavior can be customized using engine options.

- `$some_data` must be an associative array. Array keys will be variable names in the template. There are powerful tools in Foil to interact with template data inside template file. Moreover, directly passing data to `render()` is not the only way to add context for templates, Foil comes with a context API that lets you pre-assign data to templates under specific conditions.
