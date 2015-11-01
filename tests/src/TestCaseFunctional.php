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

use Foil\Foil;

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

    /**
     * @param array $options
     */
    public function initFoil(array $options = [])
    {
        $base = realpath(getenv('FOIL_TESTS_BASEPATH')).DIRECTORY_SEPARATOR;
        $options = array_merge(
            [
                'folders' => [
                    'foo' => $base.implode(DIRECTORY_SEPARATOR, ['_files', 'foo']),
                    'bar' => $base.implode(DIRECTORY_SEPARATOR, ['_files', 'bar']),
                ],
            ],
            $options
        );
        $app = Foil::boot($options);
        $this->container = $this->accessPrivateProperty('container', $app);
        $this->engine = $app->engine();
    }
}
