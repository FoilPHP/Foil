<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Functional;

use Foil\Tests\TestCaseFunctional;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class BlocksTest extends TestCaseFunctional
{

    /**
     * @coversNothing
     */
    public function testBlock()
    {
        $this->initFoil();
        $this->engine->registerBlock('wrap', function ($output, $before, $later) {
            return $before.$output.$later;
        });
        $render = $this->engine->render('foo::blocks');
        assertSame('<div><ul><li>a</li><li>a</li><li>a</li></ul></div>', $render);
    }
}
