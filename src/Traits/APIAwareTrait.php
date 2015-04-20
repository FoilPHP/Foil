<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Traits;

use Foil\API;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait APIAwareTrait
{
    private $api;

    /**
     * @return API
     */
    public function api()
    {
        return $this->api;
    }

    public function setAPI(API $api)
    {
        $this->api = $api;
    }
}
