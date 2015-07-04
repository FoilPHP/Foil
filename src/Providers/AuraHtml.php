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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Aura\Html\HelperLocatorFactory;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 *
 * @codeCoverageIgnore
 */
class AuraHtml implements ServiceProviderInterface
{
    /**
     * Registers AuraPHP/Html services on the container.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['aura.html.locatorFactory'] = function () {
            return new HelperLocatorFactory();
        };
        $container['aura.html.locator'] = function (Container $container) {
            return $container['aura.html.locatorFactory']->newInstance();
        };
        $container['aura.html.escaper'] = function (Container $container) {
            return $container['aura.html.locator']->escape();
        };
    }
}
