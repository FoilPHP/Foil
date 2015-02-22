<!--
currentMenu: "inheritance"
currentSection: "Templates"
title: "Template Inheritance"
-->

# Template Inheritance

## What's that?

Template inheritance is a technique to write DRY templates. It makes use of **layouts** to define the *skeleton* of a web page and then various specific **child templates** that *extend* layouts by defining only the parts that need to be changed.

Moreover, any child template can be used as layout for other templates: in this way is possible to write rich and complex web pages without having to repeat a single template line.

## How It works?

Essentially, a layout contains the structure of a web page, and individuate specific *areas* in the page (e.g. "header" or "footer").

Then a layout can be extended by templates that contain only the code for the areas to be modified.

After that, the engine render the HTML for a web page by *merging* common areas taken from layout and specific template areas defined in the child template.

Layout areas in Foil are called **sections** (some other template engines like [Twig](http://twig.sensiolabs.org/doc/templates.html#template-inheritance) call them *blocks*).

An example will make the concept easier to understand.

Let's define a base layout, `main.php`, which defines a simple HTML skeleton document that you might use for a simple web page.

```php
<!DOCTYPE html>
<html>
    <head>
        <?php $this->section('head') ?>
          <link rel="stylesheet" href="style.css" />
          <title><?php $this->section('title') ?>My Home Page<?php $this->stop() ?></title>
        <?php $this->stop() ?>
    </head>
    <body>
        <div id="content">
          <?php $this->section('content') ?>
          <?php $this->stop() ?>
        </div>
        <div id="footer">
          <?php $this->section('footer') ?>
            &copy; Copyright 2014 by <a href="http://example.com/">you</a>.
          <?php $this->stop() ?>
        </div>
    </body>
</html>
```

**`section()`** method is used to start a section definition. You need to pass an unique name for the section.

**`stop()`** method is used to end a section definition. There is no need to pass section name, because `stop()` ends the last opened section.

Note that is possible to create nested sections, e. g. in code above the section "title" is *inside* the section "head".

Now we can write a child template, that uses layout above and define only parts that need to be changed.

```php
<?php $this->layout('main') ?>

<?php $this->section('head') ?>
<style type="text/css">
  #welcome { color: #ff0000; }
</style>
<?php $this->append() ?>

<?php $this->section('content') ?>
<h1>Index</h1>
<p id="welcome">Welcome to my awesome homepage.</p>
<?php $this->append() ?>
```

**`layout()`** is the method used to assign a layout to a child template. It must be in the first line of a template file.

In a child template the content between `section()` and `append()` is **appended** to the same section defined in the layout.

E.g. if the child template above is rendered, `<head>` tag will contain:

```html
<head>
  <link rel="stylesheet" href="style.css" />
  <title>My Home Page</title>
  <style type="text/css">
    .important { color: #336699; }
  </style>
</head>
```

Inside a child template, is also possible to completely **replace** the content of a layout section by using **`replace()`** method to close a section.

E.g. using same `main.php` layout and a template with following code:

```php
<?php $this->layout('main') ?>

<?php $this->section('head') ?>
<link rel="stylesheet" href="style-alt.css" />
<title>A Title</title>
<?php $this->replace() ?>
```

the rendered `<head>` tag would be:

```html
<head>
  <link rel="stylesheet" href="style-alt.css" />
  <title>A Title</title>
</head>
```

In examples above, to close a section in child templates I always used `append()` or `replace()`, but is possible to use `stop()` as well,
just like in layouts (in fact any child template may be used as layout to another template).

When used inside a child template `stop()`, by default, acts just like `append()`, but is possible to make it works like `replace()`
by setting `'section_def_mode'` Foil engine option to `Foil\Contracts\SectionInterface::MODE_REPLACE`. Something like:

```php
$engine = Foil\engine([
    'folders'          => ['path/to/templates'],
    'section_def_mode' => Foil\Contracts\SectionInterface::MODE_REPLACE
]);
```

## Functions Summary

 - **`layout()`** is used to assign a layout to a template. By default a layout has access to all the
 template variables, but is possible limit or completely disable access to templates variables.
 Is also possible pass layout-specific variables. See *"Data / Pass Data to Templates"* to know more.
 Accepts as first arugument either a template name (with or withor file extension) to be searched in registered folder or a full path of a layout file.

 - **`section()`** is used to start a section definition.

 - **`append()`**, **`replace()`** and **`stop()`** are all used to end a section definition, but

   - `append()` appends the section content to same section content in layout
   - `replace()` replaces the content of same section in layout
   - `stop()` may acts as `append()` or `replace()` depending on section default mode, that by default is set to append.

   Please note that in *base* layouts (templates that don't extend any layout) or in partials, there is **no difference** among the 3 functions, because in those files there is nothing to replace or to append to.
   For this reasons I suggest to always use `stop()` in those cases to avoid confusion.

## Stacked Layouts

A basic principle of template inheritance is that a template that uses a layout can be used as layout for another template.
Foil supports this workflow with no limit on inheritance levels.

Let's write a base layout, assuming it is saved in a file named `'index.php'`

```php
<!DOCTYPE html>
<html>
    <head>
        <?php $this->section('head') ?>
          <link rel="stylesheet" href="style.css" />
          <title><?= $this->title ?></title>
        <?php $this->stop() // head ?>
    </head>
    <body>
        <div id="main">
          <?php $this->section('main') ?>
            Welcome to My Awesome Home Page
          <?php $this->stop() // main ?>
        </div>
        <div id="footer">
          <?php $this->section('footer') ?>
            &copy; Copyright 2014 by <a href="http://example.com/">you</a>.
          <?php $this->stop() // footer ?>
        </div>
    </body>
</html>
```

Now a template that uses previous layout, assuming it is saved in a file named `'blog.php'`

```php
<?php $this->layout('index') ?>

<?php $this->section('main') ?>

  <?php $this->section('content') ?>
  <section>
    Welcome to My Blog
  </section>
  <?php $this->stop() // content ?>

  <aside>
    <?= $this->insert('partials/sidebar') // insert a partial ?>
  </aside>

<?php $this->replace() // main ?>
```

And finally an article template that uses previous `'blog.php'` as layout

```php
<?php $this->layout('blog') ?>

<?php $this->section('content') ?>
<article>
  <?= $this->v('article.content') ?>
</article>
<?php $this->replace() ?>
```

This pretty trivial example gives you an idea of the power template inheritance gives to you.

In the example above there are some examples of template data access, e.g. `$this->title` in the index layout.

In facts, template properties give access to data passed to template using engine `render()` method.

Moreover, in the last template, there is the line

```php
$this->v('article.content')
```

It's a data helper: a function that facilitates the access to nested template data.

There is **a lot** more to say about template data in Foil, see *"Data"* docs section to learn more.

## Section Supply

`section()`, `stop()`, `append()` and `replace()` methods are a very powerful way to obtain template inheritance by defining a default content for a section that can be easily overridden in child templates.

But sometimes, in a template, one wants to output a section only if it is defined in any of the child templates, otherwise just output nothing.

That can be surely done by defining a empty section:

```php
<?php $this->section('foo') ?>
<?php $this->stop() ?>
```

However this looks pretty *inelegant* and probably worsens readability.

A better way to do the trick is to use the `supply()` method: it accepts a section name and returns the section content only if it is defined.

```php
<?php $this->supply('foo') // Outputs the content of 'foo' section if it is defined ?>
```

### Supply with Default

`supply()` accepts as second argument a **default content** to be used if the section is not defined.

```php
<?php $this->supply('posts', 'Sorry, no post found.') ?>
```

This is a comfortable and readable way to obtain template inheritance when the default content can be contained in one line,
for multi-line section content (way more common in real world applications) the `section()` / `stop()` method is the suggested way to go.

### Supply with Callback Default

Another interesting feature of `supply()` is that its default can be set using a callback: in that case, only if section is not defined
the callback runs and the result is used as `supply()` result. This allows *deferred* default content.

Example:

```php
$callback = function($section, $template) {
  return $template->insert($section.'-default');
}
<?php $this->supply('a-section-name', $callback) ?>
```

As shown in example above, callback passed as default argument will receive as first argument the section name, and as second argument the template object.

This allows powerful routine for default, e.g. in the example above, if a section is not defined, a partial named `"{$section}-default.php"` is loaded.

Note that if I'd *directly* used `$template->insert('a-section-name-default')` as default argument the partial fetching
had happened immediately, even if section was actually defined. By using callback default argument, the partial fetching happen only if needed.



## Template Buffer

In all the examples in this page, when there is a child template that uses a layout, all the template code is inside sections.

But if a template contains some content outside of any section, that content is **not** returned in the rendered HTML, but is stored in **template buffer**.

In any layout, `buffer()` method gives access to all the content defined outside of any section in the *direct* child template. It works seamlessly with stacked layouts.

Let's assume we have following layout code saved in a file named `base.php`  

```php
<!DOCTYPE html>
<html>
    <head>
      <title>My Awesome Page</title>
    </head>
    <body>
      <?= $this->buffer() ?>
    </body>
</html>
```

and the following template code saved in a file named `home.php`  

```php
<?php $this->layout('base') ?>

<p>Welcome to My Site</p>

<?= $this->buffer() ?>
```

and finally the following template code that uses the previous as layout

```php
<?php $this->layout('home') ?>

<p>I hope you like it.</p>
```

When the template right above is rendered, the HTML obtained will be

```html
<!DOCTYPE html>
<html>
    <head>
      <title>My Awesome Page</title>
    </head>
    <body>
      <p>Welcome to My Site</p>
      <p>I hope you like it.</p>
    </body>
</html>
```
