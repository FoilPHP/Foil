FOIL
======

> PHP template engine, for PHP templates.

-------

[![travis-ci status](https://travis-ci.org/FoilPHP/Foil.svg?branch=master)](https://travis-ci.org/FoilPHP/Foil)
[![codecov.io](http://codecov.io/github/FoilPHP/Foil/coverage.svg?branch=master)](http://codecov.io/github/FoilPHP/Foil?branch=master)
![Dependencies](https://img.shields.io/requires/github/FoilPHP/Foil.svg)
[![release](https://img.shields.io/github/release/FoilPHP/foil.svg)](https://github.com/FoilPHP/Foil/releases/tag/0.6.0)
[![license]( 	https://img.shields.io/github/license/FoilPHP/foil.svg)](http://opensource.org/licenses/MIT)

-------

**Foil** brings all the flexibility and power of modern template engines to native PHP templates. Write simple, clean and concise templates with nothing more than PHP.

# Key Features

 - Templates inheritance (you'll never miss Twig or Blade)
 - Clean, concise and DRY templates
 - Dozen of ready-made helper functions and filters
 - Easily extensible and customizable
 - Multiple template folders with file auto-discover or custom picking
 - Auto or manual data escape
 - Powerful context API (preassign data to templates using conditions)
 - Framework agnostic, centralized API for very easy integration
 - Composer ready, fully unit and functional tested, PSR-1/2/4 compliant

...and many more


# Why?

Templates engines like Twig, or Blade are a great thing, really.

However, to use them one needs to learn another *language* with its own syntax and rules.

Moreover, using compiled engines to use even a simple PHP function one needs to write engine extension.

On its side, PHP is already a templating language, but honestly it's not a good one, because it's missing pivotal features of modern template engines, like template inheritance.

## Why not Plates?

Then I discovered [Plates](http://platesphp.com/), and it was love at first sight. But, you know, that kind of love rarely lasts a lifetime.

Trying to do something *serious* with Plates, I encountered several problems which I had not when using compiled template engines.

I wrote [a blog post](http://gm.zoomlab.it/2015/template-engines-i-moved-from-love-to-meh-for-plates/) to explain why I am not happy with Plates anymore and so I decided to write Foil.

# Requirements

Foil is framework agnostic, only thing needed is PHP 5.4+ and Composer to add Foil to you PHP project.

---

# Backward Incompatibility Notice

Foil version **0.6** introduced backward incompatibility changes. Internal objects mechanism changed a lot, but
core features and especially template functions were not affected.

Please see [v0.6 release notes](https://github.com/FoilPHP/Foil/releases/tag/0.6.0) to know more on the topic.

---

# License

Foil is open source and released under MIT license. See LICENSE file for more info.

# Question? Issues?

Foil is hosted on GitHub. Feel free to open issues there for suggestions, questions and real issues.

# Who's Behind Foil

I'm Giuseppe, I deal with PHP since 2005. For questions, rants or chat ping me on Twitter ([@gmazzap](https://twitter.com/gmazzap)) or on ["The Loop"](http://chat.stackexchange.com/rooms/6/the-loop) (Stack Exchange) chat. Well, it's possible I'll ignore rants.
