<?php namespace Foil;

use Pimple\Container;
use Foil\Contracts\BootableServiceProviderInterface as Bootable;
use Pimple\ServiceProviderInterface as ServiceProvider;
use Foil\Contracts\SectionInterface;
use SplQueue;

/**
 * Instantiates and store services by instantiating Pimple container and service providers.
 * Bootable provider are booted too.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Bootstrapper
{
    private static $defaults = [
        'autoescape'       => true,
        'ext'              => 'php',
        'folders'          => [],
        'section_def_mode' => SectionInterface::MODE_APPEND,
    ];
    private $boot_queue;

    public function __construct()
    {
        $this->boot_queue = new SplQueue();
    }

    public function init(array $options, array $providers)
    {
        $container = new Container(['options' => array_merge(self::$defaults, $options)]);
        array_walk($providers, function ($class) use ($container) {
            $provider = class_exists($class) ? new $class() : false;
            $provider instanceof ServiceProvider and $container->register($provider);
            $provider instanceof Bootable and $this->boot_queue->enqueue($provider);
        });

        return $container;
    }

    public function boot($container)
    {
        while (! $this->boot_queue->isEmpty()) {
            $this->boot_queue->dequeue()->boot($container);
        }
        $container['events']->fire('f.bootstrapped');
    }
}
