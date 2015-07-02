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
use InvalidArgumentException;

/**
 * Provide context to a template if template file name matches a regex.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class RegexContext implements ContextInterface
{
    use Traits\DataHandlerTrait;

    /**
     * @var string
     */
    private $regex;

    /**
     * @var bool
     */
    private $acceptBasename = false;

    /**
     * @param string $regex
     * @param array  $data
     */
    public function __construct($regex, array $data = [])
    {
        if (! is_string($regex)) {
            throw new InvalidArgumentException('Regex must be in a string');
        }
        $this->regex = $regex;
        $this->setData($data);
    }

    /**
     * @inheritdoc
     */
    public function accept($template)
    {
        if (! is_string($template)) {
            throw new InvalidArgumentException('Template name must be in a string');
        }
        $match = $this->acceptBasename ? basename($template) : $template;

        return preg_match($this->regex, $match) === 1;
    }

    /**
     * @inheritdoc
     */
    public function provide()
    {
        return $this->data();
    }

    /**
     * @return bool
     */
    public function acceptBasename()
    {
        return $this->acceptBasename = true;
    }
}
