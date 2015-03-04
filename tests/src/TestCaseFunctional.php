<?php namespace Foil\Tests;

use Foil\Bootstrapper;

class TestCaseFunctional extends TestCase
{
    protected $engine;
    protected $container;

    public function initFoil(array $options = [])
    {
        $base = dirname(preg_replace('|[\\/]+|', DIRECTORY_SEPARATOR, FOILTESTSBASEPATH));
        $bootstrapper = new Bootstrapper();
        $options = array_merge([
            'folders' => [
                'foo' => $base.implode(DIRECTORY_SEPARATOR, ['', 'tests', '_files', 'foo']),
                'bar' => $base.implode(DIRECTORY_SEPARATOR, ['', 'tests', '_files', 'bar']),
            ],
        ], $options);
        $providers = [
            '\\Foil\\Providers\\Kernel',
            '\\Foil\\Providers\\Core',
            '\\Foil\\Providers\\Context',
            '\\Foil\\Providers\\Extensions',
        ];
        $container = $bootstrapper->init($options, $providers);
        $container['api'] = new API($container);
        $bootstrapper->boot($container);
        $this->container = $container;
        $this->engine = $container['engine'];
    }
}
