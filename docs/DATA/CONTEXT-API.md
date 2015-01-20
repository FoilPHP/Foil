<!--
currentMenu: "contextapi"
currentSection: "Data"
title: "Context API"
-->

# Context API

Pass data to Foil engine `render()` method is the simplest and preferred way to pass data to templates.

However, often one needs to pass some data to *any* template rendered or to *some* templates under specific conditions.
For example, if you want to pass the current user object to any template on a backend system.

Foil context API is just a way to pass data to any template or groups of templates without having to pass that data to `render()` engine method.

There are two ways to use Foil context API:

 - via engine methods `useData()` and `useContext()`
 - via Foil API

The latter is a simple but powerful tool to use **all** Foil features (so not only context API) and facilitate the integration of Foil in any application.
It is documented in *"Integrate Foil / API"* docs section.

## `useData()`

This engine method accepts an array of data, that will be available in **all** templates.

```php
$engine->useData(['app_name' => 'My Awesome App']);
```

Using code above, any template that is rendered using that engine object will have access to `'app_name'` variable.

Note that in case of name conflict between data assigned via context API and data directly passed to template, the latter takes precedence:

```php
$engine->render('a-template', ['app_name' => 'My Super Awesome App']);
```

Using code above, in the `a-template.php` template, `'app_name'` variable will be equal to *'My Super Awesome App'*.

## `useContext()`

This method is used to pass some data to a set of templates. Templates to pass data to can be selected in 2 ways:

 - all templates whose file path contain a string or matches a regex
 - using a custom context class

To match all templates whose file path contain a string you need to pass the string to search as 1st argument to `useContext()` and the data to pass as 2nd argument.

For example:

```php
$engine->useContext('/admin/', ['owner' => get_current_user()]);
```

Using code above all templates whose path contain the string `"/admin/"` will have access to the `'owner'` variable.

If you pass `true` as 3rd argument the string search is done using regex:

```php
$engine->useContext('/\.phtml$/', ['foo' => 'Bar'], true);
```

Using code above all templates whose extension is `.phtml` will have access to `'foo'` variable.

## Custom Context Objects

A possible issue with code above is that the code used to retrieve the data that is passed as 2nd argument to `useContext()` is always ran,
even if the template that is rendered doesn't satisfy the condition (so will not receive that data).

This is non-issue if data passed is hardcoded, e.g. like in the `['foo' => 'Bar']` example above, but if data needs to be retrieved using *expensive* code, makes no sense
run it if the template will not satisfy the condition.

To overcome this problem is possible to write custom context classes. They are classes that extends `Foil\Contracts\ContextInterface`.

That interface has just 2 methods:

 - `accept($template)`
 - `provide()`

`accept()` receives the template path that is going to be rendered, if it returns `true` all data returned by `provide()` (that must be an array) is passed to template.

Let's see an usage example:

```php
namespace MyApp;

use Foil\Contracts\ContextInterface;

class BlogMenuContext implements ContextInterface
{
    private $menu;

    public function __construct(MenuModel $menu)
    {
        $this->menu = $menu;
    }

    public function accept($template)
    {
        return strstr($template, DIRECTORY_SEPARATOR.'blog') !== false;
    }

    public function provide()
    {
        return ['menu' => $this->menu->getItems()];
    }
}
```

The `accept()` method in class above receives template path and return true only if it contains *"/blog"*.

**Only in that case** `provide()` method runs and, using menu model, gets menu items that are passed to template in `'menu'` variable.

If this class is available, it can be used passing an instance of it to `useContext()` engine method:

```php
$engine->useContext(new BlogMenuContext());
```

You can think at context classes as a sort of *inverted controllers*: normally controllers pass data to views based on external conditions (e.g. which route matched),
when using context classes are the views that *claims* data.

This approach makes a lot of sense for view-specific data that there is no reason a controller is aware of.
For example, why a controller should be aware which styles or scripts a template uses?

A similar approach is used in [Laravel View Composers](http://laravel.com/docs/4.2/responses#view-composers), but Foil context API is more flexible because

 - is framework agnostic
 - context classes are not aware of view object, so is possible to use any kind of view object or no view object at all
 - how to match a template is left to user, where in Lavarel is only possible to exactly match a template name or a set of template names
