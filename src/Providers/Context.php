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
use Foil\Context\Collection as ContextCollection;
use Foil\Contracts\TemplateInterface as Template;
use Foil\Contracts\ContextInterface;
use Foil\Context\GlobalContext;

/**
 * Provider for template context objects.
 * Holds main collection and setup/listen to events that allow data to be passed to templates.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
class Context implements BootableServiceProviderInterface
{
    /**
     * Register context service
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['context'] = function ($c) {
            return new ContextCollection($c['events']);
        };
    }

    /**
     * Add the event that allow passing Context to template
     *
     * @param Container $container
     */
    public function boot(Container $container)
    {
        $container['events']->on('f.template.prerender',
            function (Template $template) use ($container) {
                if ($container['context']->accept($template->path())) {
                    $template->setData($container['context']->provide());
                    $container['context']->disallow();
                }
            });

        $container['events']->on('f.engine.call', function ($func, array $args) use ($container) {
            switch ($func) {
                case 'useData':
                    $this->engineAddData($container['context'], $args);
                    break;
                case 'useContext':
                    $this->engineAddContext($container['context'], $args);
                    break;
            }
        });
    }

    /**
     * @param \Foil\Context\Collection $collection
     * @param array                    $args
     */
    private function engineAddData(ContextCollection $collection, array $args)
    {
        if (is_array($args[0])) {
            $collection->add(new GlobalContext($args[0]));
        }
    }

    /**
     * @param \Foil\Context\Collection $collection
     * @param array                    $args
     */
    private function engineAddContext(ContextCollection $collection, array $args)
    {
        if ($args[0] instanceof ContextInterface) {
            $collection->add($args[0]);
        }
        if (is_string($args[0]) && isset($args[1]) && is_array($args[1])) {
            $regex = isset($args[2]) && ! empty($args[2]);
            $class = $regex ? '\\Foil\\Context\\RegexContext' : '\\Foil\\Context\\SearchContext';
            $collection->add(new $class($args[0], $args[1]));
        }
    }
}
