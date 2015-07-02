<!--
currentMenu: "alias"
currentSection: "Getting Started"
title: "Alias"
-->

# Alias

Inside templates, all the Foil features are accessed via the `$this` variable,
e.g. template variables are accessed like `$this->varName` and template function like `$this->functionName()`.

Even if the word *"this"* is made by just 4 letters, being typed more and more times in a template requires a lot of typing.

For example, accessing a variable like `$variable` is pretty shorter than `$this->variable`.

I am strongly convinced that benefits coming from Foil approach completely worth the additional 6 letters needed to access a variable.

However, starting from version 0.6, Foil provides a way to shorten its syntax.

## Introducing Alias Variable

Alias variable is a global variable available in templates that aliases `$this`.

E.g. using the variable `$T` as alias, a template variable can be accessed using `$T->variable`
as well as a template function using `$T->functionName()`.

The alias must be set, using the `'alias'` option, when Foil Engine is instantiated:


```php
$engine = Foil\engine([
  'folders' => ['path/to/templates'],
  'alias'   => 'T',
]);
```

the only constraint is that the alias must be a valid variable name. E.g. something like:

```php
$engine = Foil\engine([
  'folders' => ['path/to/templates'],
  'alias'   => 'Foil',
]);
```

is totally fine, and allows to access Foil features in templates using `$Foil->` syntax.
