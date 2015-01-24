<!--
currentMenu: "partials"
currentSection: "Templates"
title: "Partials"
-->

# Partials

Partials are template files that are included in other templates.

They are commonly used for small parts that are repeated among templates or layouts.

By default partials have access to all current context (template variables), but is possible to limit or completely
disable access to it. It is also possible to pass partial-specific variables.
See *"Data / Pass Data to Templates"* to know more on the topic.

Partials are inserted in a template using `insert()` method.

```php
<div id="sidebar">
  <?= $this->insert('partials/sidebar') ?>
</div>
```

`insert()` accepts as first argument the partial name to be inserted and **returns** its content.
So you need to echo what is returned by `insert()` to make the content visible.

Partials are searched in all registered folders, just like standard templates. All the rules that apply to Foil engine methods `render()` and `find()` are valid for `insert()` too: default extension, specific folders targeting, and so on.
See *"Templates / Overview"* to know more on the topic.

## Conditional Insertion

When you call `insert()` with a non-existent template name an exception is thrown.

Sometimes may be desirable to insert a partial only if it exists, without any error if it isn't there.
That can be done with `insertif()` that accepts exact same arguments of `insert()`.

Once `insertif()` returns nothing if template is not found, it can be easily used to output a default content when the partial doesn't exist:

```php
<?= $this->insertif('header') ?: 'Default Header' ?>
```

Note that another partial can be used as default:

```php
<?= $this->insertif('header') ?: $this->insertif('default-header') ?>
```

## Sections Definition in Partials

Foil supports sections definition in partials (See *"Templates / Inheritance"* to know more about sections).

For example, let's assume in a layout, named `home.php`, there's the following code:

```php
<!DOCTYPE html>
<html>
    <head>
      <title>My Awesome Page</title>
    </head>
    <body>
      <div id="main">
        Welcome!
      </div>
      <div id="sidebar">
        <?= $this->insert('partials/sidebar') ?>
      </div>
    </body>
</html>
```

and in the partials `partials/sidebar.php` there is the code:

```php
<?php $this->start('sidebar-top') ?>
  <div>
    <h3>Sidebar</h3>
  </div>
<?php $this->stop() ?>

<?php $this->start('sidebar-main') ?>
  <div>
    <p>Default sidebar.</p>
  </div>
<?php $this->stop() ?>
```

In a template that extends `home.php` is possible to extends / replace the content of `'sidebar-top'` or `'sidebar-main'`
sections even if they were defined in a partial:

```php
<?php $this->layout('home') ?>

<?php $this->start('sidebar-main') ?>
  <div>
    <p>Custom sidebar.</p>
  </div>
<?php $this->replace() ?>
```
