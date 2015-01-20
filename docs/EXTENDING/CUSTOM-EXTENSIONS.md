<!--
currentMenu: "extensions"
currentSection: "Extending Foil"
title: "Custom Foil Extensions"
-->

# Custom Foil Extensions

A Foil extension is a class that is used to register functions and filters.

Technically speaking it's a class that implements `Foil\Contracts\ExtensionInterface`.

It has just 3 methods:

 - `provideFilters()` this method must return an array where keys are filter names and values are filter callbacks.
  You need to return an empty array if an extension does not register any filter.
 - `provideFunctions()` this method must return an array where keys are function names and values are function callbacks.
 You need to return an empty array if an extension does not register any function.
 - `setup(array $args)` this method is called when the extension is loaded and receives the array of arguments that is passed as 2nd argument for `loadExtension()`

So, basically, an extension is a way to register more filters and functions at once and has the additional benefit to be configured via arguments.

This is how a basic Foil extension looks like:

```php
namespace MyApp;

use Foil\Contracts\ExtensionInterface;

class FooExtension implements ExtensionInterface
{
    private $args;

    public function setup(array $args)
    {
        $this->args = $args;
    }

    public function provideFilters()
    {
        return [
          'foo' => [$this, 'foo'],
        ];
    }

    public function provideFunctions()
    {
        return [
          'bar' => [$this, 'bar']
        ];
    }

    public function foo($input)
    {
        return $input.'Foo';
    }

    public function bar()
    {
      return isset($this->args['bar']) ? $this->args['bar'] : 'Bar!';
    }
}
```

Note that there is nothing that forces you to register extension own methods as functions and filters, in fact any valid PHP callback can be used.

Once the extension class is available is possible to load it using `loadExtension()` engine method. Something like:

```php
$engine->loadExtension(new MyApp\FooExtension(), ['bar' => 'Bar! Bar!']);
```

Second argument passed to `loadExtension()` is the array that will passed to `setup()` method.

## Safe Functions

As better explained in the *"Extending Foil / Custom Functions & Filters"* Foil functions by default can't output HTML content, because their output is HTML-encoded.

When you register a single functions via `$engine->registerfunction()` is possible to pass `true` as 3rd argument to mark the function as *safe* so that it is allowed to output HTML.

When you load an extension that provides functions that needs to output HTML, you need to register those function as safe as well.

That can be done using 3rd argument for `loadExtension()`. It can be:

 - `true`, to mark as safe all the functions provided by the extension
 -  an array of function names, to mark as safe only the explicitly passed functions

What said above only apply if the engine has autoescape turned on (default).


## Template Aware Extensions

It may be desirable to access the template object inside an extension.
In fact, having access to template object is possible to call on it any of the methods that you can call on `$this` inside a template file, including other extensions functions and filters.

To write an extension that is aware of template object you need to implement `Foil\Contracts\TemplateAwareInterface` interface.
You can write custom implementation for that interface methods, but Foil provides a trait: `Foil\Traits\TemplateAwareTrait` that contains an implementation for all interface methods, doing the work for you.

The *skeleton* of a template-aware extension looks like so:

```php
namespace MyApp;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\TemplateAwareInterface;
use Foil\Traits\TemplateAwareTrait;

class FooExtension implements ExtensionInterface, TemplateAwareInterface
{
    use TemplateAwareTrait;

    public function setup(array $args)
    {
    }

    public function provideFilters()
    {
    }

    public function provideFunctions()
    {
    }

}
```

In a template-aware extension, like the one above, **`$this->template()`** gives access to current template object.

## Finder Aware Extensions

Several Foil methods accept template names and internally *translate* them into real full file paths.
That is done via a finder service that appends default file extension if no one is provided and then searches file into registered folders.
(See *"Templates / Folders"* for more info).

It may be desirable to do same thing inside an extension class.
That can be done by making the extension class aware of the finder service.

To do that you need to implement the `Foil\Contracts\FinderAwareInterface` and implement its methods.

The simplest way to to that is to use the `Foil\Traits\FinderAwareTrait`.

The *skeleton* of a finder-aware extension looks like so:

```php
namespace MyApp;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\FinderAwareInterface;
use Foil\Traits\FinderAwareTrait;

class FooExtension implements ExtensionInterface, FinderAwareInterface
{
    use FinderAwareTrait;

    public function setup(array $args)
    {
    }

    public function provideFilters()
    {
    }

    public function provideFunctions()
    {
    }

}
```

In a finder-aware extension, like the one above, is possible to use **`$this->find($template)`** to find templates by their names,
in the exact same way you can call `$engine->find($template)` when you have access to engine object.


## API Aware Extensions

Foil ships with a powerful tool: an API object that helps to integrate Foil in any system, giving access to **all** Foil features.

If you need to develop an extension that interacts very deep with Foil internal objects,
you can build an extension that is aware of that API object, obtaining access to all Foil features.

To do that you need to implement the `Foil\Contracts\APIAwareInterface` and implement its methods.

The simplest way to to that is to use the `Foil\Traits\APIAwareTrait`.

The *skeleton* of an API-aware extension looks like so:

```php
namespace MyApp;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\APIAwareInterface;
use Foil\Traits\APIAwareTrait;

class FooExtension implements ExtensionInterface, APIAwareInterface
{
    use APIAwareTrait;

    public function setup(array $args){
    }

    public function provideFilters(){
    }

    public function provideFunctions()
    {
    }

}
```

In an API-aware extension, like the one above, is possible to use **`$this->api()`** to get the API object and call on it all supported methods,
see *"Integrate Foil"* docs section to know which they are what they do.

## The Base Extension Class

To speed-up extension development, instead of implementing one or more _*Aware_ interfaces, is possible to extend the `Foil\Extensions\Base` abstract class.

That class:

 - is API aware, so you can use API object in it to access all Foil features
 - implements all the 3 methods of `ExtensionInterface`:
   - `setup()` is implemented just saving the arguments received in the `$args` object property
   - `provideFilters()` is implemented by returning an empty array
   - `provideFunctions()` is implemented by returning an empty array

  In this way is possible to write extensions that provide only filters (or only functions) overriding only the related method
 - has a method `option()` that lets you access to Engine options, e.g. current setting for autoescape, template folders, default file extension and so on
