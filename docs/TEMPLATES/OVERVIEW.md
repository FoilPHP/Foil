<!--
currentMenu: "templatesoverview"
currentSection: "Templates"
title: "Overview"
-->

# Templates

In this documentation the word "templates" is used to refer to *template files*, that are the PHP files that contain markup.

Internally, Foil also has a class named "Template" but in your code you might never need to *directly* interact with that class.
As better explained in the *"Integrate Foil"* docs section, you can integrate Foil with your code passing around Foil engine object and/or Foil API object.

It worth to say here that template files are included in the context of templates objects.

To better understand this concept, look at the following pseudo-code:

```php
namespace Foil\Template;

class Template {

  function __construct($template_path)
  {
    $this->path = $template_path;
  }

  function render()
  {
    return include($this->path);
  }
}
```

Pseudo-code above is an over-semplification of what happen internally in Foil when a template is rendered.
I shown it to better explain some basic concepts of Foil templates:

 - templates are just regular PHP files, treated as regular PHP files, you can and should use any PHP code: it will just work
 - the variable `$this` inside template files refers to an instance of Foil template class.
 However, template data you can access with `$this->variable_name` or template functions like `$this->function_name()` are **not** real object properties or methods, but they are handled using PHP overloading methods [`__get`](http://php.net/manual/en/language.oop5.overloading.php#object.get) and [`__call`](http://php.net/manual/en/language.oop5.overloading.php#object.call)

A note on **namespaces**. In PHP every file has its own namespace, so even if template files are included in the context of template objects don't assume `Foil\Template` as current namespace inside templates: if you don't define any namespace in template files (and you probably shouldn't) namespace will be the root one.

## Get Templates Path (inside a template)

If inside a template you need to know the *real* absolute path of the file you can just use **`$this->path()`**.

Any template can have a layout (see *"Templates / Inheritance"*).
**`$this->layoutPath()`** inside a template file returns the real absolute path of template layout.

## Get Templates Path (outside any template)

Having access to the Foil engine instance, is possible to get a template path using `find()` engine method that takes a template name and return the absolute path of template file.

```php
$engine->find('a-template'); // returns: /path/to/templates/a-template.php
```

Same method can be used to check if a template file exists: it returns `false` if template doesn't exist.

`find()` follows all the rules for Foil methods that accept template names:

- to pass the template file extension is optional, if not provided default extension will be used
- if there are more templates with same name, but in different folders, the absolute path of the first template found is returned, where folders are searched in registration order
- to search a template in a specific folder is possible to use the *double colon syntax*:

     ```php
     $engine->find('folder-id::template-name');
     ```
  to do that, the folder have to be registered into the engine using folder ID.

See  *"Getting Started / File Extensions"* to know more about default extension and  *"Getting Started / Folders"* to know more about folder IDs.
