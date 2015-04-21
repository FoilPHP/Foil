<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Context;

use Foil\Contracts\ContextInterface;
use Foil\Traits;

/**
 * Provide context to all templates.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class GlobalContext implements ContextInterface
{
    use Traits\DataHandlerTrait;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * @inheritdoc
     */
    public function accept($template)
    {
        return is_string($template);
    }

    /**
     * @inheritdoc
     */
    public function provide()
    {
        return $this->data();
    }
}
