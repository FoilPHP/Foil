<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Providers;

use Foil\Contracts\BootableServiceProviderInterface;
use Pimple\Container;
use Foil\Contracts\ExtensionInterface as Extension;
use Foil\Contracts\TemplateAwareInterface as TemplateAware;
use Foil\Contracts\FinderAwareInterface as FinderAware;
use Foil\Contracts\EngineAwareInterface as EngineAware;
use Foil\Kernel\Command;
use Foil\Kernel\Events;
use Foil\Kernel\Escaper;

/**
 * Kernel services service provider
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
class Kernel implements BootableServiceProviderInterface
{
    /**
     * Register all core services.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['escaper'] = function (Container $c) {
            return new Escaper($c['aura.html.escaper'], $c['options']['default_charset']);
        };
        $container['command'] = function (Container $c) {
            return new Command($c['escaper'], $c['options']['autoescape']);
        };
        $container['events'] = function () {
            return new Events();
        };
    }

    /**
     * Register events to allow registration of extensions, filters and actions.
     * Events are triggered by Engine.
     *
     * @param Container $container
     */
    public function boot(Container $container)
    {
        // register an extension
        $container['events']->on(
            'f.extension.load',
            function (Extension $extension, array $options, $safe) use ($container) {
                $extension->setup($options);
                $container['command']->registerFunctions($extension->provideFunctions(), $safe);
                $container['command']->registerFilters($extension->provideFilters());
                if ($extension instanceof TemplateAware) {
                    $extension->setStack($container['template.stack']);
                }
                if ($extension instanceof FinderAware) {
                    $extension->setFinder($container['template.finder']);
                }
                if ($extension instanceof EngineAware) {
                    $extension->setEngine($container['engine']);
                }
                $container['events']->fire('f.extension.registered', $extension);
            }
        );

        // register a function
        $container['events']->on(
            'f.function.register',
            function ($function, callable $callback, $safe) use ($container) {
                $container['command']->registerFunctions([$function => $callback], $safe);
            }
        );

        // register a filter
        $container['events']->on(
            'f.filter.register',
            function ($filter, callable $callback) use ($container) {
                $container['command']->registerFilters([$filter => $callback]);
            }
        );

        $container['events']->on('f.bootstrapped', function () use ($container) {
            $container['command']->lock();
        });
    }
}
