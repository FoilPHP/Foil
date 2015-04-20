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

use Foil\Contracts\BootableServiceProviderInterface as BootableServiceProvider;
use Pimple\Container;
use Foil\Extensions\Sections;
use Foil\Extensions\Helpers;
use Foil\Extensions\Walker;
use Foil\Extensions\Filters;

/**
 * Core extensions service provider
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Extensions implements BootableServiceProvider
{
    /**
     * Register extension services
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['extensions.sections'] = function ($c) {
            return new Sections($c['section.factory']);
        };

        $container['extensions.helpers'] = function ($c) {
            return new Helpers($c['options']);
        };

        $container['extensions.walker'] = function () {
            return new Walker();
        };

        $container['extensions.filters'] = function () {
            return new Filters();
        };
    }

    public function boot(Container $container)
    {
        // we must be sure all other providers have been booted before register extensions
        // because events that allow registration are attached on Core provider boot.
        $container['events']->on('f.bootstrapped', function () use ($container) {
            $this->loadExtensions($container);
        });
    }

    private function loadExtensions($container)
    {
        $extensions = ['sections', 'helpers', 'walker', 'filters'];
        array_walk($extensions, function ($extension) use ($container) {
            $container['engine']->loadExtension($container["extensions.{$extension}"], [], true);
        });
    }
}
