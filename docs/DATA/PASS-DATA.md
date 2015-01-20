<!--
currentMenu: "passdata"
currentSection: "Data"
title: "Pass Data To Templates"
-->

# Pass Data To Templates

The most obvious and straightforward way to pass data to templates is to pass an associative array
as second argument for `render()` engine method.

Something like:

```php
Foil\engine(['folders' => 'path/to/templates'])
  ->render(
    'template-example',
    [
      'foo' => 'Bar',
      'bar' => 'Baz',
      'baz' => 'Foo'
    ]
  );
```

using code above the variables `'foo'`, `'bar'` and `'baz'` will be available in the template.

Another way to pass data to templates is to use the **Foil Context API**. See *"Data / Context API"* docs section
to know how it works.

What it worth to say here is that data assigned via Context API is merged with any data passed to `render()`
and that the latter takes precedence on the former.

## Variables Scope

In Foil, layout and partials used in a template, by default, will inherit data assigned to template.
(To know more about layouts and partials see *"Templates"* docs section).

However, both for layouts and partials, is possible to override inherited data.

That is done passing a data array as second argument to `layout()` and `insert()` methods respectively.
(To know more about these methods see *"Templates"* docs section).

Assuming the following code:

```php
Foil\engine(['folders' => 'path/to/templates'])
  ->render('template-example', ['foo' => 'Template Foo', 'bar' => 'Baz']);
```

and assuming that in the `template-example.php` file there is:

```php
// layout() is the method used to declare a layout
$this->layout('layout-name', ['foo' => 'Layout Foo']);

// insert() is the method used to include a partial in current template
$this->insert('partial-name', ['foo' => 'Partial Foo']);
```

The variable "foo" will evaluate as:

- "Template Foo" in the `template-example.php` template file
- "Layout Foo" in the `layout-name.php` layout file
- "Partial Foo" in the `partial-name.php` partial file

the variable "bar" will be "Baz" in all 3 files.

Note that data inheritance follow template inheritance: partials will inherit data from current context,
i.e. if a partial is included in a layout, it will inherit data from layout.

## Layout and Partial Data Filtering

As said above, by default, layouts and partials will inherit all data from current context.
However is possible to disable access to all or some context variables.
That is done by using an array of allowed context variable names as third argument for `layout()` or `insert()`.

E.g. assuming a layout is declared with following code:

```php
$this->layout('layout-name', ['var_name' => 'A variable'], ['foo']);

// same goes for $this->insert('partial-name', ['var_name' => 'A variable'], ['foo']);
```

in the `layout-name.php` layout file the only available variables will be `'var_name'` (explicitly passed to layout) and `'foo'`, inherited from active context.

Using an empty array as third argument only context explicitly passed variables (if any) will be available in the layout / partial.
