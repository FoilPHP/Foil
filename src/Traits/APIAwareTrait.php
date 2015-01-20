<?php namespace Foil\Traits;

use Foil\API;

/**
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait APIAwareTrait
{
    private $api;

    public function api()
    {
        return $this->api;
    }

    public function setAPI(API $api)
    {
        $this->api = $api;
    }
}
