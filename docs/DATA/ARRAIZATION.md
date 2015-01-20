<!--
currentMenu: "arraization"
currentSection: "Data"
title: "Arraization"
-->

# Arraization

With "Arraization" I mean the process to convert to array all the objects that are passed to a template.

There is a benefit unrelated with Foil on doing this: templates becomes decoupled from internal data objects and at any time you can change internal data layer implementation.

However, benefits of arraization in Foil are more than this, you also get:

 - **automatic escape** for everything: the function Foil uses to escape data is capable to escape strings and arrays (even deeply nested) containing strings,
   so if objects are converted to arrays there's nothing that Foil can't escape
 - **dot syntax** to access nested data
 - there are Foil **helpers** specifically designed to work with arrays

See *"Data / Retrieve Data in Templates"* to know more on automatic escape and dot syntax.

Of course, you can use **any method** and **any library** you like to convert data into array (maybe you know [Fractal](http://fractal.thephpleague.com/)),
but Foil comes with a function that can worthily do the trick.

# `arraize()`

The only function that handles arraization is `Foil\arraize()`.

This function recursively convert arrays and *traversable* objects into nested arrays.

All the strings contained in the data can be optionally HTML-encoded.

Graph below resumes how this function works:

---

[![Arraize graph](http://zoomlab.it/public/arraize.png)](http://zoomlab.it/public/arraizebig.png)

---

What you don't see from graph is that final obtained value is casted to array, it means that even if you pass a single scalar value (a string, an integer) it is converted in an array with one element.

Aside that, graph make clear that **whatever data you pass to the function at the end of the process, you get an array that contains no objects**, because:

 - traversable objects are looped and each item is saved into an array and eventually recursively processed
 - non-traversable objects are converted using the first available in a set of 5 methods:

   1. if a transformer (in form of callback, object or class) was passed to `arraize()` it is used. More on transformer later in this page.
   2. if the object has a method `toArray()` it is called (and result is casted to array, just in case). This brings out-of-the-box support to Eloquent models.
   3. if the object has a method `asArray()` it is called (result is casted to array)
   4. if the object implements `JsonSerializable` interface, the method `jsonSerialize()` is called and result is casted to array
   5. if none of the previous is available `get_object_vars()` is called to obtain an array of object properties

Note that the array obtained is recursively processed.

## Transformers

If your models don't support none of the method used by default by `arraize()`, is always possible to add a `toArray()` or an `asArray` method.

But if you don't want or you can't edit your models, `arraize()` supports transformers: they are callbacks to convert object into arrays.

To use transformers you need to pass to `arraize()` an associative array where

 - keys are the fully-qualified class names of the objects to be transformed
 - values are the tranformers to apply

Tranformers (the array values) can be passed in 3 ways:

 - as callbacks that receive the object and have to return an array
 - as object instances, that don't need to implement any interface or extend any class, they just need a method `transform()` that receives the object and has to return an array
 - as fully-qualified class names whose instances are created by `arraize()` before calling on them `transform()` method.

## Example

Below there are some examples for the usage of transformers.

The `Product` class that will be used in all examples is very simple:

```php
namespace My\App;

class Product
{
  private $name;
  private $price;

  public function __construct($name, $price) {
    $this->name = $name;
    $this->price = $price;
  }

  public function getName() {
    return $this->name;
  }

  public function getPrice() {
    return $this->price;
  }  
}
```

And this is the array of products I'll use for the examples:

```php
$products = [
  'samsungs516'  => new My\App\Product('Samsung S5 16GB', 580),
  'iphone6p128'  => new My\App\Product('iPhone 6 Plus 128GB', 949),
  'xiaominote64' => new My\App\Product('Xiaomi Mi Note 64GB', 450),
];
```

Note that even if in your application you use some sort of `Collection` class instead of arrays, it will not be a problem if
that collection object is traversable, and very likely it is.

### Example With Transformer Callback

Assuming `arraize()` is called like so:

```php
$transformers = [
  'My\App\Product' => function($product) {
    return [
      'model' => 'Model: '.$product->getName(),
      'price' => 'Price: <b>'.number_format($product->getPrice(), 2).' $</b>',
    ];
  }
];

$arraized = Foil\arraize($products, true, $transformers);
```

the `$arraized` array will be equal to:

```php
[
    'samsungs516' => [
        'model' => 'Model: Samsung S5 16GB',
        'price' => 'Price: &lt;b&gt;580.00 $&lt;/b&gt;'
    ],
    'iphone6p128' => [
        'model' => 'Model: iPhone 6 Plus 128GB',
        'price' => 'Price: &lt;b&gt;949.00 $&lt;/b&gt;'
    ],
    'xiaominote64' => [
        'model' => 'Model: Xiaomi Mi Note 64GB',
        'price' => 'Price: &lt;b&gt;450.00 $&lt;/b&gt;'
    ]
]
```

So the callback passed in `$transformers` array, using as key the product objects class name, has been used to covert all objects into arrays.

Note that:

 - if the array of products would be passed in a deeply nested array the conversion had be performed in exactly same way
 - all strings have been HTML encoded, that happen because I used `true` as 2nd argument for `arraize()`, setting it to `false` no encoding happen
 - I used an anonymous function, but you can also pass any other kind of callback

### Example With Transformer Object

The same result of above could be obtained using a transformer object, i.e. an object that has a public `transform()` method.

```php
namespace My\App;

class ProductTransformer
{
  public function transform(Product $product)
  {
    return [
      'model' => 'Model: '.$product->getName(),
      'price' => 'Price: <b>'.number_format($product->getPrice(), 2).' $</b>',
    ];
  }
}
```

Calling `arraize()` like so:

```php
$arraized = $Foil\arraize($products, true, ['My\App\Product'=> new My\App\ProductTransformer()]);
```

the output would be the same.

### Example With Transformer Class

If you don't need any specific configuration for transformer objects (e.g. any arguments to pass to constructor), you can pass a transformer class name instead of
a transformer object, and `arraize()` will instantiate the class for you.

e.g. previous example could be wrote like so:

```php
$arraized = $Foil\arraize($products, true, ['My\App\Product'=> 'My\App\ProductTransformer']);
```

# Usage for `render()`

A nice usage case for `arraize()` is to covert all (or part of) the data to pass to Foil engine `render()` method.

Below an example in a trivial controller class:

```php
use Foil;

class MyController
{
  private $foil;

  function __construct(Foil\Engine $foil)
  {
    $this->foil = $foil;
  }

  function getProducts(ProductsRepository $products)
  {
    $data = $products->getAll();

    return $this->foil->render('product-archive', ['products' => Foil\arraize($data, true)]);
  }
};
```

Note that if you use `arraize()` to convert the whole data passed to templates, can be a good idea set Foil autoescape option to `false` and pass `true` as second argument
to `arraize()`: all data will escaped anyway and it will be more performant.

However, in that way **all** data will be HTML-encoded, so that's not recommended if your data contain HTML content that you want to preserve, unless there is just one (or a few)
variable that contains trusted HTML content.

In such case, probably best choice is keep escaping everything then only decode trusted content using the `d()` data helper or the `decode()` function.

Also remember that if autoescape is turned off, you can register custom functions that output raw HTML content.

---

If you want to avoid direct calls to `Foil\arraize()` to decouple your application classes from Foil,
you can make use of the Foil API object. See *Integrate Foil / Overview* to know more on the topic.


## Notes For `a()` And `araw()` Data Helpers

The data helpers `a()` and `araw()`, internally make use of `arraize()` function, so they can be used to convert specific template variables.

However, when using that helpers you have less control on how the function is used, keep in mind that:

- `$escape` argument for the function is always set to false when you use `araw()`, and is set to `true` for `a()` when autoescape engine option is set to true
- you **can't** pass transformers to `arraize()` when using data helpers. You you have that need pass converted data to `render()` or, as alternative, use Context API.
