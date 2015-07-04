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
use Foil\Extensions\AuraHtml as AuraHtmlExtension;

/**
 * Core extensions service provider
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
class Extensions implements BootableServiceProvider
{
    /**
     * Register extension services.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['extensions.sections'] = function ($c) {
            return new Sections($c['section.factory'], $c['events']);
        };

        $container['extensions.helpers'] = function ($c) {
            return new Helpers($c['escaper'], $c['options']);
        };

        $container['extensions.walker'] = function ($c) {
            return new Walker($c['command'], $c['escaper'], $c['options']);
        };

        $container['extensions.filters'] = function () {
            return new Filters();
        };

        $container['extensions.aura_html'] = function ($c) {
            return new AuraHtmlExtension(
                $c['aura.html.locator'],
                $c['options']['html_tags_functions']
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        // we must be sure all other providers have been booted before register extensions
        // because events that allow registration are attached on Core provider boot.
        $container['events']->on('f.bootstrapped', function () use ($container) {
            $this->loadExtensions($container);
        });
    }

    /**
     * @param \Pimple\Container $container
     */
    private function loadExtensions(Container $container)
    {
        $extensions = ['sections', 'helpers', 'aura_html', 'walker', 'filters'];
        array_walk($extensions, function ($extension) use ($container) {
            /** @var \Foil\Engine $engine */
            $engine = $container['engine'];
            $engine->loadExtension($container["extensions.{$extension}"], [], true);
        });
    }
}
