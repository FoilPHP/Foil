<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Template;

use ArrayAccess as AA;
use Foil\API;
use Foil\Contracts\APIAwareInterface as APIAware;
use Foil\Traits;
use InvalidArgumentException;

/**
 * Factory and holds templates object instances.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Factory implements APIAware
{
    use Traits\APIAwareTrait;

    const DEFAULT_CONTRACT = '\\Foil\\Contracts\\TemplateInterface';
    const DEFAULT_CLASS    = '\\Foil\\Template\\Template';

    /**
     * @var string
     */
    private $contract;

    /**
     * @var \ArrayAccess
     */
    private $templates;

    /**
     * @var \ArrayAccess
     */
    private $sections;

    /**
     * @var string
     */
    private $defaultClass;

    /**
     * @param \ArrayAccess $templates
     * @param \ArrayAccess $sections
     * @param \Foil\API    $api
     * @param null|string  $contract
     */
    public function __construct(AA $templates, AA $sections, API $api, $contract = null)
    {
        if (! is_string($contract) || ! interface_exists($contract)) {
            $contract = self::DEFAULT_CONTRACT;
        }
        $this->contract = $contract;
        $this->templates = $templates;
        $this->sections = $sections;
        $class = $api->option('template_class');
        $this->defaultClass = $class && class_exists($class) ? $class : self::DEFAULT_CLASS;
        $this->setAPI($api);
    }

    /**
     * Factory and/or returns template objects.
     *
     * @param  string                            $path       Full path to template file
     * @param  string                            $class_name A custom template class name
     * @return \Foil\Contracts\TemplateInterface
     * @throws InvalidArgumentException
     */
    public function factory($path, $class_name = null)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException('Template path must be in a string.');
        }
        if (! $this->templates->offsetExists($path)) {
            $class = $this->getClass($class_name);
            $this->templates[$path] = new $class($path, $this->sections, $this->api());
        }

        return $this->templates[$path];
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
            || ! in_array(ltrim($this->contract, '\\'), class_implements($class), true)
        ) {
            return $this->defaultClass;
        }

        return $class;
    }
}
