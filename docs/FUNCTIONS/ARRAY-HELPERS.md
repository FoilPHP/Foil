<!--
currentMenu: "arrayhelpers"
currentSection: "Functions"
title: "Array Helpers"
-->

# Array Helpers

Array helpers are a set of functions to facilitate operation on arrays.
All array helpers needs an array to be passed as first argument.
The helper `a()` (documented under *Data / Helpers*) can be used to ensure a template variable is returned as an array.

They are:

- `chunk()`
- `isFirst()`
- `isLast()`
- `index()`
- `index0()`

## `chunk()`

It works in the same way of `"chunk"` filter (documented under *Data / Filters*).
It chunks an array by returning an array of arrays with the given number of items.
It is also possible to provide a value to fill missing items.

Below there is an example of how this function can be used in combination with loop helpers to write
concise and effective templates with very few lines of code:

```php
// Assuming 'items' template variable is equal to range('A', 'E');

arraw_walk($this->chunk($this->a('items'), 2, '&nbsp;'), function($row) {
  echo $this->walkWrap($row, '<div class="row">$s</tr>', '<div class="col-md-6">%s</div>');
});

/*
previous code outputs:

<div class="row">
  <div class="col-md-6">A</div>
  <div class="col-md-6">B</div>
</div>
<div class="row">
  <div class="col-md-6">C</div>
  <div class="col-md-6">D</div>
</div>
<div class="row">
  <div class="col-md-6">E</div>
  <div class="col-md-6">&nbsp;</div>
</div>
*/
```

It worth noting that if the array is not defined or is empty the code above output nothing, without any error and without
the need to check for variable existence or proper type.

## `isFirst()`

Given an array and a value returns true if that value is the first element in the array.

```php
foreach($array as $item) {
  printf($this->isFirst($array, $item) ? '<p class="first">%s</p>' : '<p>%s</p>', $item);
}
```

## `isLast()`

Given an array and a value returns true if that value is the last element in the array.
See `isFirst()` above for usage example.

## `index()`

Given an array and a value returns the numerical index (1-based) of the value inside the array.
If the given value is not present in the array returns `-1`.

If a third argument with a numerical index is passed to function, it returns true if the index is the one
that the value has in the array (1-based).

It works for associative and non-associative arrays.

```php
$a = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'];

$this->index($a, 'A');    // 1
$this->index($a, 'A', 1); // true
$this->index($a, 'F');    // -1
$this->index($a, 'A', 2); // false
```

## `index0()`

Identical to `index()`, but the indexes are 0-based.
