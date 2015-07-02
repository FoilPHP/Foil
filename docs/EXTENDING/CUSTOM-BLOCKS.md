<!--
currentMenu: "customblocks"
currentSection: "Extending Foil"
title: "Custom Blocks"
-->

# Custom Blocks

Custom blocks are callback that perform operations on a block of HTML that is delimited in templates using
`block()` and `endblock()` template methods.

Please have a look at the section "*Blocks & Helpers / Blocks*" to know more about blocks.

## Add a custom block

Adding a block callback is as easy as pass a block name and a block callback to the `Engine::registerBlock()` method.

For example, let's write a callback that takes a block of text, and output every new line in a separate `<li>` tag, all wrapped in a `<ul>` tag.

```php
$engine->registerBlock(
   'eol2ul',
   function($html) {
      // remove white space at boundaries an normalize end of lines
      $trim = trim(str_replace("\r\n", "\n", $html));
      // explode by end of lines
      $exploded = explode("\n", $trim);
      // implode lines into a list
      return '<ul><li>'.implode('</li><li>', $exploded).'</li></ul>';
   }
);
```

After that, in a template:

```php
<?php $this->block('eol2ul') ?>

a
b
c

<?php $this->endblock() ?>
```

Will output:

```html
<ul>
    <li>a</li>
    <li>b</li>
    <li>c</li>
</ul>
```

## Blocks with arguments

Blocks callbacks may accept a variadic number of arguments that can be passed to `block()` function right after block name.

A simple example that will covert given words in a block of text to links to the Google search url for that word:

```php
$engine->registerBlock(
   'googleize',
   function() {
      $args = func_get_args();
      $html = array_shift($args); // block of HTMl is first argument
      $words = $args; // all the words passed ot open() are now in $args array
      $format = '<a href="https://www.google.com#q=%s" target="_blank">%s</a>';
      foreach($words as $word) {
        $html = str_replace($word, sprintf($format, urlencode($word), $word), $html);
      }
      return $html;
   }
);
```

In template...

```php
<?php $this->block('googleize', 'Lorem', 'adipiscing') ?>

Lorem ipsum dolor sit amet,
consectetur adipiscing elit.

<?php $this->endblock() ?>
```

The output will be:

```html
<a href="https://www.google.com#q=Lorem" target="_blank">Lorem</a> ipsum dolor sit amet,
consectetur <a href="https://www.google.com#q=adipiscing" target="_blank">adipiscing</a> elit.
```

