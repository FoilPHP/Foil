<?php namespace Foil\Tests;

use Foil\Bootstrapper;

class TestCaseFunctional extends TestCase
{
    protected $engine;
    protected $container;

    public function setUp()
    {
        parent::setUp();
        $base = dirname(preg_replace('|[\\/]+|', DIRECTORY_SEPARATOR, FOILTESTSBASEPATH));
        $bootstrapper = new Bootstrapper();
        $options = [
            'folders' => [
                'foo' => $base.implode(DIRECTORY_SEPARATOR, ['', 'tests', '_files', 'foo']),
                'bar' => $base.implode(DIRECTORY_SEPARATOR, ['', 'tests', '_files', 'bar']),
            ],
        ];
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
