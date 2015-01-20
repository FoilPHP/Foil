<?php namespace Foil\Context;

use Foil\Contracts\ContextInterface;
use Foil\Traits;
use InvalidArgumentException;

/**
 * Provide context to a template if a string is present in the template file name.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class SearchContext implements ContextInterface
{
    use Traits\DataHandlerTrait;

    private $needle = '';

    public function __construct($needle, array $data = [])
    {
        if (! is_string($needle)) {
            throw new InvalidArgumentException('Needle must be in a string');
        }
        $this->needle = $needle;
        $this->setData($data);
    }

    public function accept($template)
    {
        if (! is_string($template)) {
            throw new InvalidArgumentException('Template name must be in a string');
        }

        return strstr($template, $this->needle) !== false;
    }

    public function provide()
    {
        return $this->data();
    }
}
