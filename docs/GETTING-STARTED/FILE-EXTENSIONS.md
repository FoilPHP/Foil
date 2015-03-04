<!--
currentMenu: "fileexts"
currentSection: "Getting Started"
title: "File Extensions"
-->

# File Extensions

Template names can be passed to all methods that accept template names, e.g. `render()`, without file extension and default extension, that is `.php`, will be added by Foil.

To change default extension is possible to use `"ext"` engine option:

```php
$engine = Foil\engine([
  'ext' => 'phtml'
]);
```

## Manually set extension

Default file extension is not the only one that can be used for templates. In facts, is possible to pass template names with an extension, Foil will load the template even if it has not the default extension:

```php
$engine->render('a-template.inc', $some_data);
```
