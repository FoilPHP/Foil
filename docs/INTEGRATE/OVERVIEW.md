<!--
currentMenu: "integrateoverview"
currentSection: "Integrate Foil"
title: "Overview"
-->

# Integrate Foil

## Dependency Injection

For the great majority of the use cases, the only Foil component your code needs to interact with is the Foil engine object.

That object can be obtained using **`Foil\engine()`** function that may receives an array of options to configure Foil behavior. See *"Getting Started / Engine"* for more details.

Best way to integrate Foil in your OOP project is to *inject* an instance of the engine into your application objects (controllers most of the times).

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

In any class that has access to Foil engine object, you can make use of pretty all Foil features:

 - render templates (of course)
 - check for template existence and/or find template paths by their names
 - extend Foil features with extensions, functions and filters
 - pass data via Context API

## Functional API Approach

Object oriented design is not the only way to write PHP code.

Functional programming is becoming increasingly popular nowadays, and even if surely PHP is not the best language to build functional code,
it can be used for the scope, and more and more PHP packages and extensions are released for that purpose.

Foil is **not** built using functional code, but is designed to be easily integrated in application that are.

First of all note how you don't instantiate Foil engine using standard OOP approach (`$engine = new Foil\engine()`), but you use a function instead.

Engine initialization is just one of the things that can be done using functions in Foil, in fact, there is an entire set of API functions (all under `Foil` namespace)
that can be used to use all Foil features. (Full list available at *"Integrate Foil / API functions"*)

Even better, using API functions is possible to do, very easily, things that using OOP would require a tight coupling between Foil and consumer code, and a deep knowledge of internal used Foil objects.

So, is functional programming the preferred way to use Foil? Not really, thanks to the **Foil API object**.

## Foil API object

Foil API object is a class on which you call all the API functions, just like they are class methods.

You will probably be surprised that the this sentence, that explain what API object is, contains almost same characters of the entire [class code](https://github.com/Giuseppe-Mazzapica/Foil/blob/master/src/API.php).

If you followed the link to class code, you've seen that the only thing that API class do is to run namespaced functions that have the same name of any method you call on it.

For example let's take a Foil API function we already seen, `Foil\engine()`:

```php
// functional API
$engine = Foil\engine($options);

// OOP API
$api = new Foil\API();
$engine = $api->engine($options);
```

The 2 ways to obtain Foil engine instance used in code above are equivalent and interchangeable.

So, the net effect of using the API object is that the Foil API functions can be *ported* to OOP applications seamlessly.

In fact, passing around API object with dependency injection is possible to write well designed and testable OOP code that integrates perfectly with Foil.

In the page *"Integrate Foil / API functions"* there is the complete list of available Foil API functions.
