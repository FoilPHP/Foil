<!--
currentMenu: "folders"
currentSection: "Getting Started"
title: "Folders"
-->

# Folders

When a template is required, Foil will search for it inside registered template folders. There are two ways to add folders to the engine:

 1. to use `"folders"` option for `Foil\engine()`
 2. to call `addFolder()` method on the engine instance that `Foil\engine()` returns

Both methods can be used in combination:

```php
$engine = Foil\engine([
  'folders' => ['path/to/templates']
]);

$engine->addFolder('another/path/to/templates');

$engine->render('a-template', $some_data);
```

In the example above a file named `a-template.php` is first searched in `'path/to/templates'` folder and if not found there, Foil will search for it in `'another/path/to/templates'` folder.

There is no limit in the number of folders can be added, but note that a large number may slow down template find process, because Foil have to search any folder until it founds the required template.

It is possible to keep templates *in order* and at same time register just a few folders by using **subfolders**.

For example, if you registered the path `'path/to/templates'` and you have a template named `'article'` inside the folder `'path/to/templates/blog'`,
you don't need to register this subdirectory, but just to pass subfolder name to `render()`:

```php
$engine->render('blog/article', $some_data);
```

## Target specific folders

What said above means that if two or more files with same name exist in different folders, only the first found (where folder are searched in registration order) is used. This behavior may not be desirable, e.g. if you want to render a template in a specific folder. Foil allows to target specific folders by assigning ID to folders.

Both folders registration methods support folder IDs:

 - when folders are passed as `"folders"` argument for `Foil\engine()` is possible assign IDs to folders using an associative array, where each key is the folder ID.
 - when using `addFolder` method, the second argument can be a folder ID

Example:

```php
$engine = Foil\engine([
  // ID for this folder will be "main"
  'folders' => ['main' => 'path/to/templates']
]);

// ID for this folder will be "secondary"
$engine->addFolder('another/path/to/templates', 'secondary');
```

Once folders are registered with IDs, is possible to target a specific folder inside `render()` method (and in all the methods that accepts template names) using folder ID and a double column before template name:

```php
$engine->render('secondary::template-name', $some_data);
```
