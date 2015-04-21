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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
trait DataHandlerTrait
{
    /**
     * @var array
     */
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
