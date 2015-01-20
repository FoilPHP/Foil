<?php namespace Foil\Context;

use Foil\Contracts\ContextInterface;
use Foil\Traits;
use InvalidArgumentException;

/**
 * Provide context to a template if template file name matches a regex.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class RegexContext implements ContextInterface
{
    use Traits\DataHandlerTrait;

    private $regex;
    private $accept_basename = false;

    public function __construct($regex, array $data = [])
    {
        if (! is_string($regex)) {
            throw new InvalidArgumentException('Regex must be in a string');
        }
        $this->regex = $regex;
        $this->setData($data);
    }

    public function accept($template)
    {
        if (! is_string($template)) {
            throw new InvalidArgumentException('Template name must be in a string');
        }
        $match = $this->accept_basename ? basename($template) : $template;

        return preg_match($this->regex, $match) === 1;
    }

    public function provide()
    {
        return $this->data();
    }

    public function acceptBasename()
    {
        return $this->accept_basename = true;
    }
}
