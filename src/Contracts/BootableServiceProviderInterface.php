<?php namespace Foil\Contracts;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * A service provider that can be booted after all providers have been registered.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface BootableServiceProviderInterface extends ServiceProviderInterface
{
    /**
     * Boot a service provide after all provides have been registered, so using other provides
     * code it's fine here.
     *
     * @param Container $container
     */
    public function boot(Container $container);
}
