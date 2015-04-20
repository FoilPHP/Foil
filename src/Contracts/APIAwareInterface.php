<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Contracts;

use Foil\API;

/**
 * Object that "knows" API instance.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface APIAwareInterface
{
    /**
     * Return the api instance
     *
     * @return API
     */
    public function api();

    /**
     * Set the api instance
     *
     * @param API $api
     */
    public function setAPI(API $api);
}
