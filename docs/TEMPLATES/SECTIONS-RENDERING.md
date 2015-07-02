<!--
currentMenu: "rendersections"
currentSection: "Templates"
title: "Sections Rendering"
-->

# Sections Rendering

Sometimes may be desirable being able to render just one (or more) section of a template instead of
just getting the rendering result of whole template.

Since version 0.6, Foil allows to do that.

(See *"Template Inheritance"* to know more about sections.)

There are two Engine methods dedicated to this task:

 - `renderSection()`
 - `renderSections()`

## `Engine::renderSection()`

This method can be used to render one or more given sections of a template.

For example, let's take a simple template like the following:


```php
<?php $this->section('who') ?>

    <?= $this->username ?> is the current user.

<?php $this->stop() ?>


<?php $this->section('what') ?>

    Current page is <?= $this->page ?>.

<?php $this->stop() ?>
```

and let's assume it is saved in a `foo.php` template file.

Doing:

```php
$data = ['username' => 'Giuseppe', 'page' => 'Sections Rendering' ];

$who_section = $engine->renderSection('foo', 'who', $data);
```

`$who_section` will be equal to: **`"Giuseppe is the current user."`**.

So the signature of `renderSection()` is:

 - first argument is the template name
 - second argument is the section name(s) to render
 - third argument the data to pass to templates

Passing as second argument a section name, like done above, it will return a string, that is the result of section rendering.

Passing  as second argument an array of section names, the method returns an associative array where keys
are section names and values are result of related section rendering.

E.g. by doing:

```php
$data = ['username' => 'Giuseppe', 'page' => 'Sections Rendering' ];

$sections = $engine->renderSection('foo', ['who', 'what'], $data);
```

the value of `$sections` will be:

```php
[
    'who'  => 'Giuseppe is the current user.',
    'what' => ' Current page is Sections Rendering.'
]
```

## `Engine::renderSections()`

This method is very similar to `renderSection()` when used passing an array of sections.

However, it does not accepts any section name and returns an array of **all** the sections rendered.

E.g., assuming same template as above, the code:

```php
$data = ['username' => 'Giuseppe', 'page' => 'Sections Rendering' ];

$sections = $engine->renderSections('foo', $data);
```

the value of `$sections` will be:

```php
[
    'who'  => 'Giuseppe is the current user.',
    'what' => ' Current page is Sections Rendering.'
]
```

So, the signature of `renderSections()` is the same of the `render()` method, but returns the array of
rendered sections instead of the string that is the result of the whole template rendering.