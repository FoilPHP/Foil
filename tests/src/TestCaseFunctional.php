<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests;

use Foil\Bootstrapper;
use Brain\Monkey\Functions;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class TestCaseFunctional extends TestCase
{
    /**
     * @var \Foil\Engine
     */
    protected $engine;

    /**
     * @var \Pimple\Container
     */
    protected $container;

    public function initFoil(array $options = [])
    {
        Functions::when('Foil\entities')->alias(function ($var, $strategy = 'html') {
            return $this->container['escaper']->escape($var, $strategy);
        });
        $base = dirname(preg_replace('|[\\/]+|', DIRECTORY_SEPARATOR, FOILTESTSBASEPATH));
        $bootstrapper = new Bootstrapper();
        $options = array_merge(
            [
                'folders' => [
                    'foo' => $base.implode(DIRECTORY_SEPARATOR, ['', 'tests', '_files', 'foo']),
                    'bar' => $base.implode(DIRECTORY_SEPARATOR, ['', 'tests', '_files', 'bar']),
                ],
            ],
            $options
        );
        $providers = [
            '\\Foil\\Providers\\Kernel',
            '\\Foil\\Providers\\AuraHtml',
            '\\Foil\\Providers\\Core',
            '\\Foil\\Providers\\Context',
            '\\Foil\\Providers\\Extensions',
            '\\Foil\\Providers\\Blocks',
        ];
        $container = $bootstrapper->init($options, $providers);
        $container['api'] = new API($container);
        $bootstrapper->boot($container);
        $this->container = $container;
        $this->engine = $container['engine'];
    }
}
