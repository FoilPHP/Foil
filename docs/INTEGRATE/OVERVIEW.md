<!--
currentMenu: "integrateoverview"
currentSection: "Integrate Foil"
title: "Overview"
-->

# Integrate Foil

## Dependency Injection

For the great majority of the use cases, the only Foil component your code needs to interact with is the Foil engine object.

So the best way to integrate Foil in your OOP project is to *inject* an instance of the engine into your application objects (controllers most of the times).

This is an example:

```php
use Foil\Engine;

class MyController
{
  private $foil;

  function __construct(Engine $foil)
  {
    $this->foil = $foil;
  }

  function getIndex()
  {
    return $this->foil->render('home', ['message' => 'Welcome to my site.']);
  }
}
```

To know hot to obtain an instance of `Foil\Engine` to pass to controller, see *"Getting Started / Engine"*.

In any class that has access to Foil engine object, you can make use of pretty all Foil features:

 - render templates (of course)
 - check for template existence and/or find template paths by their names
 - extend Foil features with extensions, functions and filters
 - pass data via Context API

## Warning

Version v0.6 completely changed how Foil internally works, **introducing backward compatibility breaks**, the main are:

 - most of the functions under `Foil` namespaces have been removed
 - the `Foil\API` object has been removed (as a consequence the `APIAware` interface has been removed as well)
 - `Foil\engine()` function that used to return same instance of Engine (~ singleton), now returns a *"fresh"* instance on any call.

These changes mostly affected the writing of custom extensions, in fact, Foil **core features and template methods are unchanged**.
