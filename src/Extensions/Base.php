<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;
use Foil\Contracts\APIAwareInterface as APIAware;
use Foil\Traits\APIAwareTrait;

/**
 * Base API-aware class for extensions
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
abstract class Base implements ExtensionInterface, APIAware
{
    use APIAwareTrait;

    /**
     * @var array
     */
    private $args;

    public function setup(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @return array
     */
    public function provideFilters()
    {
        return [];
    }

    /**
     * @return array
     */
    public function provideFunctions()
    {
        return [];
    }

    /**
     * @param  string|null $option
     * @return mixed
     */
    public function option($option = null)
    {
        return $this->api()->option($option);
    }
}
