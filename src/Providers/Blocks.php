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

use Foil\Blocks\Factory;
use Foil\Contracts\BootableServiceProviderInterface;
use Pimple\Container;
use Foil\Blocks\Blocks as BlocksBucket;
use Foil\Extensions\Blocks as BlocksExtensions;
use Foil\Blocks\Spaceless;
use Foil\Blocks\Repeat;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 *
 * @codeCoverageIgnore
 */
class Blocks implements BootableServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container['blocks.factory'] = function () {
            return new Factory();
        };
        $container['blocks'] = function (Container $container) {
            return new BlocksBucket($container['blocks.factory']);
        };
        $container['extensions.blocks'] = function (Container $container) {
            return new BlocksExtensions($container['blocks']);
        };
        $container['extensions.blocks.spaceless'] = function () {
            return new Spaceless();
        };
        $container['extensions.blocks.repeat'] = function () {
            return new Repeat();
        };
    }

    /**
     * @inheritdoc
     */
    public function boot(Container $container)
    {
        // event used to register a block
        $container['events']->on(
            'f.block.register',
            function ($block, callable $callback) use ($container) {
                is_string($block) and $container['blocks']->add($block, $callback);
            }
        );

        /** @var \Foil\Blocks\Blocks $bucket */
        $bucket = $container['blocks'];

        $blocks = ['spaceless', 'repeat'];
        array_walk($blocks, function ($block) use ($container, $bucket) {
            /** @var callable $callback */
            $callback = $container["extensions.blocks.{$block}"];
            $bucket->add($block, $callback);
        });

        /** @var \Foil\Engine $engine */
        $engine = $container['engine'];
        $engine->loadExtension($container['extensions.blocks'], [], true);
    }
}
