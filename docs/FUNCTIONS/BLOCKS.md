<!--
currentMenu: "blocks"
currentSection: "Blocks & Helpers"
title: "Blocks"
-->

# Blocks

Blocks are parts of a template that before being outputted pass through a callback.

Blocks were added in version 0.6.

## Blocks characteristics

 - a block is opened with `block()` and closed with `endblock()`
 - `block()` takes as 1st argument the name fo the block, and a variadic number of arguments that are then passed to block callback
 - blocks can be nested
 - `endblock()` may *optionally* take as 1st argument the name of the block to close. When given, Foil checks that it is the last opened block
   and throws an exception if not. This is useful for nested blocks.

## Shipped Blocks

At the moment there are 2 blocks shipped with Foil:

 - `"spaceless"`
 - `"repeat"`

## Repeat

The most simple block is "repeat". This is an  example of how it works:

```php
<?php $this->block('repeat', 3) ?>

<p>say again</p>

<?php $this->endblock() ?>
```

The code above will output:

```html
<p>say again</p>
<p>say again</p>
<p>say again</p>
```

In short, it is very similar to a `for` loop, and if fact it was added mainly to easily explain how blocks work.

## Spaceless

Inspired by [`spaceless` Twig tag](http://twig.sensiolabs.org/doc/tags/spaceless.html), it removes whitespaces from a block of HTML.

May be useful in some situations, e.g. with some CSS frameworks that requires no space between certain elements.

Example:

```php
<?php $this->block('spaceless') ?>

    <div>
        <strong>foo</strong>
    </div>

<?php $this->endblock() ?>
```

Will output:

```html
<div><strong>foo</strong></div>
```

## Custom Blocks

In next versions of Foil is possible that more blocks will be added, but the real power of blocks resides in the
possibility to write custom blocks that are no more than callbacks that perform operations on a block of HTML.

It is an easy and straightforward way to write functions that accept even big blocks of HTML without but using a nice looking and easily readable syntax in templates.

How to write custom blocks is documented in *"Extending Foil / Custom Blocks"* section.