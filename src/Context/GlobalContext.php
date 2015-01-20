<?php namespace Foil\Context;

use Foil\Contracts\ContextInterface;
use Foil\Traits;

/**
 * Provide context to all templates.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class GlobalContext implements ContextInterface
{
    use Traits\DataHandlerTrait;

    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    public function accept($template)
    {
        return is_string($template);
    }

    public function provide()
    {
        return $this->data();
    }
}
