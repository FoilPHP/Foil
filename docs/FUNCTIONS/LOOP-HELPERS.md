<!--
currentMenu: "loophelpers"
currentSection: "Blocks & Helpers"
title: "Loop Helpers"
-->

# Loop Helpers

PHP has some language constructs to handle arrays, like `for`, `foreach` or `while` and also quite a lot array functions, like `array_map`, `array_walk`, `array_reduce` and a lot of others.
However array functions have some flaws:
- don't support traversable or iterators objects
- aren't consistent in signature, e.g. some of them accepts array as first argument, some as second

Moreover, in templates, you need to check for variable existence and proper type to avoid errors e.g. passing a string or a boolean to any of them will cause an error.

Foil Loop Helpers are functions that, basically, call `sprintf` on each element of arrays or traversable objects.

The variable to loop is retrieved using Foil dot syntax, simplifying the process of getting data from nested structures without the need to check for variable existence or proper type.

Every helper as two names, an extended one (for the ones who prefer readability) and a shorter alias (for the ones who prefer concise code).

They are:

- `walk()` (alias `w()`)
- `walkIf` (alias `wif()`)
- `walkWrap` (alias `ww()`)
- `walkWrapIf` (alias `wwif()`)

Each of them accepts as first argument the variable to loop through.
It can be passed as a variable name, using Foil dot syntax, or is also possible to pass an array or a traversable object.

## `walk()` / `w()`

The simplest loop helper is `walk()` (alias `w()`). It calls `sprintf` to every item of structure, applying a given format string.

Format string must contain at least a placeholder, that will be replaced with the value from the structure that is being walked.
It is possible to use a format with more than one placeholder and pass to the helper the additional values to fill the placeholders.

```php
// assuming 'items' template variable is range('a', 'c')

echo $this->walk('items', '<li>%s</li>'); // echo <li>a</li><li>b</li><li>c</li>

echo $this->walk('items', '<li>%2$s: %1$s</li>', 'Item'); // echo <li>Item: a</li><li>Item: b</li><li>Item: c</li>
```

If items variable in code above was not defined, empty, or not traversable, code above would print nothing, without any error.

Another nice feature of `walk()` (and other loop helpers) is that if each item of the array is an array as well, loop helpers will call `vsprintf` (instead of `sprintf`) for each every item. This is better explained with an example:

```php
/*
Assuming 'people' template var is equal to:

[
  ['Tom', 'London', 30],
  ['Dick', 'New York', 33],
  ['Harry', 'Berlin', 25]
]
*/

$this->walk('people', '<p>%1$s is %3$d years old and comes from %2$s</p>');

/*
Outputs:

<p>Tom is 30 years old and comes from London</p>
<p>Dick is 33 years old and comes from New York</p>
<p>Harry is 25 years old and comes from Berlin</p>
*/
```

## `walkIf()` / `wif()`

`walkIf()` (alias `wif()`) is very similar to `walk()`, but it accepts a condition to run the loop: if given condition is a falsey value
then the helper return nothing. Condition may be also passed as a callback whose returned value is used to choose if run the loop or not.

```php
// prints menu items as list items if $user['allowed'] template var is true
echo $this->wif('menu.items', $this->v('user.allowed'), '<li>%s</li>');

// prints menu items as list items if a_custom_callback() function returns true
echo $this->wif('menu.items', 'a_custom_callback', '<li>%s</li>');
```

As demonstrated in code above, combining loop helpers with data helpers is possible to write in one line effective template parts that had required
a lot more code using only PHP functions.

## `walkWrap()` / `ww()`

This helper acts just like `walk()`, but accepts an additional format string to wrap looped items.
Nice thing is that if the variable to loop is not defined or empty, even the "wrapper" code is not printed, saving an `if` block.

```php
echo $this->ww('menu.items', '<ul>%s</ul>', '<li>%s</li>');

// previous line is equivalent to:

if ($this->v('menu.items')) {
  echo '<ul>' . $this->w('menu.items', '<li>%1$s</li>') . '</ul>';
}
```


## `walkWrapIf()` / `wwif()`

As you can guess from the name, this helper is a combination of `walkWrap()` and `walkIf()`.

```php
echo $this->wwif('menu.items', $this->v('user.allowed'), '<ul>%s</ul>', '<li>%s</li>');
```
