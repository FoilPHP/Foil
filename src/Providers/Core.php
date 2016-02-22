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

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Foil\Section\Factory as SectionFactory;
use Foil\Template\Factory as TemplateFactory;
use Foil\Template\Finder;
use Foil\Template\Stack as TemplateStack;
use Foil\Engine;
use ArrayObject;

/**
 * Main services service provider
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @codeCoverageIgnore
 */
class Core implements ServiceProviderInterface
{
    /**
     * Register all core services.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $this->registerFinder($container);
        $this->registerSections($container);
        $this->registerTemplate($container);
        $this->registerEngine($container);
    }

    /**
     * @param \Pimple\Container $container
     */
    private function registerFinder(Container $container)
    {
        $container['template.finder'] = function ($c) {
            $ext = is_string($c['options']['ext']) ? $c['options']['ext'] : 'php';
            $finder = new Finder($ext);
            if (is_array($c['options']['folders']) && ! empty($c['options']['folders'])) {
                $finder->in($c['options']['folders']);
            }

            return $finder;
        };
    }

    /**
     * @param \Pimple\Container $container
     */
    private function registerSections(Container $container)
    {
        $container['section.sections'] = function () {
            return new ArrayObject();
        };
        $container['section.factory'] = function ($c) {
            return new SectionFactory($c['section.sections'], $c['options']['section_def_mode']);
        };
    }

    /**
     * @param \Pimple\Container $container
     */
    private function registerTemplate(Container $container)
    {
        $container['template.templates'] = function () {
            return new ArrayObject();
        };
        $container['template.factory'] = function ($c) {
            return new TemplateFactory(
                $c['template.templates'],
                $c['section.factory'],
                $c['command'],
                $c['options']
            );
        };
        $container['template.stack'] = function ($c) {
            return new TemplateStack($c['template.factory']);
        };
    }

    /**
     * @param \Pimple\Container $container
     */
    private function registerEngine(Container $container)
    {
        $container['engine'] = function ($c) {
            return new Engine($c['template.stack'], $c['template.finder'], $c['events']);
        };
    }
}
