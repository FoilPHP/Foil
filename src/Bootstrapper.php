<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil;

use Pimple\Container;
use Foil\Contracts\BootableServiceProviderInterface as Bootable;
use Pimple\ServiceProviderInterface as ServiceProvider;
use Foil\Contracts\SectionInterface;
use SplQueue;

/**
 * Instantiates and store services by instantiating Pimple container and service providers.
 * Bootable providers are booted too.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Bootstrapper
{
    /**
     * @var array
     */
    private static $defaults = [
        'autoescape'          => true,
        'strict_variables'    => false,
        'ext'                 => 'php',
        'folders'             => [],
        'section_def_mode'    => SectionInterface::MODE_APPEND,
        'html_tags_functions' => false,
    ];

    /**
     * @var \SplQueue
     */
    private $boot_queue;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->boot_queue = new SplQueue();
    }

    /**
     * @param  array             $options
     * @param  array             $providers
     * @return \Pimple\Container
     */
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

    /**
     * @param \Pimple\Container $container
     */
    public function boot(Container $container)
    {
        while (! $this->boot_queue->isEmpty()) {
            $this->boot_queue->dequeue()->boot($container);
        }
        $container['events']->fire('f.bootstrapped');
    }
}
