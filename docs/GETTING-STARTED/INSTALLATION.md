<!--
currentMenu: "installation"
currentSection: "Getting Started"
title: "Installation"
-->

# Installation

To install Foil you need:

 - PHP 5.4+
 - [Composer](https://getcomposer.org/)

Foil is available on Packagist, so the only thing you need to do is to add it as a dependency for your project.

That can be done by running following command in your project folder:

```shell
composer require foil/foil:0.*
```

As alternative you can directly edit your `composer.json` by adding:

```json
{
  "require": {
    "foil/foil": "~0.1"
  }
}
```

After that, only be sure to include composer autoload file:

```php
require 'vendor/autoload.php';
```

## Dependencies

Foil needs 3 very tiny, fast and great quality libraries to work:

 - [Pimple](http://pimple.sensiolabs.org/)
 - [Événement](https://github.com/igorw/evenement)
 - [get-in](https://github.com/igorw/get-in)

They will be installed for you by Composer. All of them are released under MIT license just like Foil.

When installed in development mode, Foil also requires:

 - [PHPUnit](https://phpunit.de) (MIT)
 - [Mockery](http://docs.mockery.io/en/latest/) (BSD-3-CLAUSE)
