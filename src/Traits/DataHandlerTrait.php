<?php namespace Foil\Traits;

/**
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait DataHandlerTrait
{
    private $__data = [];

    /**
     * Data setter
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->__data = $data;
    }

    /**
     * Data getter
     *
     * @return array
     */
    public function data()
    {
        return $this->__data;
    }
}
