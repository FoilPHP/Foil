<!--
currentMenu: "folders"
currentSection: "Getting Started"
title: "Folders"
-->

# Folders

When a template is required, Foil will search for it inside registered template folders.

An array of folders where to search for templates can be passed to `Foil\engine()` method (or to `Foil::boot()` method) as part of the options array, using the key `"folders"`:

```php
$engine = Foil\engine([
  'folders' => ['/path/to/templates']
]);

$engine->render('a-template', $some_data);
```

The code above looks for the file `/path/to/templates/a-template.php`.

It is possible to keep templates *in order* and at same time register just a few folders by using **subfolders**.

For example, if you registered the path `'/path/to/templates'` and you have a template named `'article'` inside the folder `'/path/to/templates/blog'`,
you don't need to register this subdirectory, but just to pass subfolder name to `render()`:

```php
$engine->render('blog/article', $some_data);
```

## Change Engine folders after initialization

After `Engine` class have been instantiated, there is still possibility to change the folders where it looks for template files:

 - using `Engine::addFolder()` method
 - using `Engine::setFolders()` method
 - using `Finder::in()` method

### Engine::addFolder()

The easiest way is to use `Engine::addFolder()` method. As its name suggests, this method adds a folder where engine looks for templates, without touching the others.

```php
$engine = Foil\engine([
  'folders' => ['/path/to/templates']
]);

$engine->addFolder('/another/path/to/templates');

$engine->render('a-template', $some_data);
```

In the example above, the file `a-template.php` is first searched in `'/path/to/templates'` folder and if not found there, Foil will search for it in `'/another/path/to/templates'` folder.

There is no limit in the number of folders can be added, but note that a large number may slow down template finding process, because Foil have to search any folder until it founds the required template.

### Engine::setFolders()

This method accepts an array of folders and can be used to completely replace any already added folder.

### Finder::in()

This method directly acts on the `Finder` class that is used by Foil to find templates in filesystem.

To obtain the instance of `Finder` where to call the method you need to call the `$engine->finder()` method.

`Finder::in()` method takes 2 arguments, an array of folders (just like `Engine::setFolder()`) and a boolean value `$reset`.

When `$reset` is true, `Finder::in()` is exactly equivalent to call `Engine::setFolders()`, so you may prefer to use the latter, as more easily accessible.

When `$reset` is false, all the passed folders are:

 - added if the ID associated to them is not yet registered (a sort of *batch* `Engine::addFolder()`)
 - updated if the ID associated to them is already registered

To understand what *"the ID associated to folders"* means read next section.

## Target specific folders

When two or more files with same name exist in different registered folders, only the first found (where folder are searched in registration order) is used.

This behavior may not be desirable, e.g. if you want to render a template in a specific folder.

Foil allows to target specific folders by assigning **ID** to folders.

All folders registration methods support folder IDs:

 - when using methods that accept folders as array, IDs are the array keys
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

Once folders are registered with IDs, is possible to target a specific folder inside `render()` method (and in all the methods that accept template names) using folder ID and a double column before template name:

```php
$engine->render('secondary::template-name', $some_data);
```

### Finder::in() and folders names

As said above, `Finder::in()` method accepts an array of folders and its behaviour, when `$reset` argument is `false`, depends on the registered folders IDs.

An example should clarify how it works:

```php
$engine = Foil\engine([
  'folders' => [
     'one' => 'path/to/templates/one',
     'two' => 'path/to/templates/two',
   ]
]);
```

And somewhere after that

```php
$finder = $engine->finder();

$finder->in([
  'three' => 'path/to/templates/three',
  'one'   => 'path/to/templates/alternative/one',
]);
```

After that, the registered folders will be:

```php
[
  'one'   => 'path/to/templates/alternative/one', // this have been edited
  'two'   => 'path/to/templates/two'
  'three' => 'path/to/templates/three'            // this have been added
]
```

So

 - the folder with ID `'one'` has been updated, because its ID was already registered
 - the folder with ID `'three'` has been added, because its ID was not registered

Please note how `Finder::in()` was not able to change the order of folders.

In fact, after `Finder::in()` was called, the folder with ID `'one'` is still the first folder
where Foil will look to templates, even if it points to a different folder.

If you need to change the order of folders, you need to set `$reset` argument to `true` or use `Engine::setFolders` method.

Those methods can be used in combination with `Finder::dirs()` method that return the array of all folders currently in use.

**Please note**: `$reset` argument was introduced in version `0.6`.


## All folders have IDs

When registering folders (no matter the method) the folders "ID" is always optional: methods that accepts an array of folders
do not complain if the array is not an *associative* array, and when using `Engine::addFolder()` method, the second argument is optional.

However, Foil **always assign an ID to a folder** if is not *manually* assigned.

The automatically assigned ID is made by the folder name and its direct folder parent name joined with a dot.

E.g. the ID assigned to the folder `/path/to/templates` would be `to.templates`.

This may brings some problems, e.g. the two paths:

 - `/one/path/to/templates`
 - `/another/path/to/templates`

are assigned to same ID, and Foil will be not capable to distinguish between the two.

This may appear an awful behavior, however it make pretty easy to access a specific folder by ID in a predictable way
even when folders are registered with no IDs.

If you are afraid of (or you are experimenting) such conflicts, always register folders using unique IDs.