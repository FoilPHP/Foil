<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Section;

use Foil\Contracts\SectionInterface;
use InvalidArgumentException;
use ArrayAccess;

/**
 * Factory and holds section object instances.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Factory
{
    const DEFAULT_CONTRACT = '\\Foil\\Contracts\\SectionInterface';
    const DEFAULT_CLASS    = '\\Foil\\Section\\Section';

    /**
     * @var string
     */
    private $contract;

    /**
     * @var \ArrayAccess
     */
    private $sections;

    /**
     * @var int
     */
    private $defaultMode;

    /**
     * @param \ArrayAccess $sections
     * @param null|int     $defaultMode
     * @param null |string $contract
     */
    public function __construct(ArrayAccess $sections, $defaultMode = null, $contract = null)
    {
        if (! is_string($contract) || ! interface_exists($contract)) {
            $contract = self::DEFAULT_CONTRACT;
        }
        $this->contract = $contract;
        $this->sections = $sections;
        $this->defaultMode = $defaultMode;
    }

    /**
     * Factory a section instance (if it was not already factored) and return it.
     *
     * @param  string                           $name      Section name
     * @param  int|bool                         $mode      Section mode, one of the mode const
     * @param  string                           $className Full qualified section class name
     * @return \Foil\Contracts\SectionInterface
     * @throws InvalidArgumentException
     */
    public function factory($name, $mode = false, $className = null)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException('Section name must be in a string.');
        }
        if (! $this->has($name)) {
            $class = $this->getClass($className);
            $this->sections[$name] = new $class($mode, $this->defaultMode);
        } else {
            $merge = $mode === SectionInterface::MODE_REPLACE ? false : true;
            $this->get($name)->setMode($mode, $merge);
        }

        return $this->sections[$name];
    }

    /**
     * Checks if a section was already factored.
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->sections->offsetExists($name);
    }

    /**
     * Checks if a section was already factored.
     *
     * @param  string $name
     * @return bool
     */
    public function get($name)
    {
        return $this->sections->offsetGet($name);
    }

    /**
     * Checks that a given class name implements factory contract and returns it (or default if
     * not).
     *
     * @param  string $class
     * @return string
     * @access private
     */
    public function getClass($class)
    {
        if (
            ! is_string($class)
            || ! class_exists($class)
            || ! is_subclass_of($class, $this->contract)
        ) {
            return self::DEFAULT_CLASS;
        }

        return $class;
    }

    public function flush()
    {
        $this->sections = new \ArrayObject();
    }
}
