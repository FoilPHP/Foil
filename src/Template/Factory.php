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

use Foil\Engine;
use Foil\Kernel\Command;
use Foil\Section\Factory as SectionFactory;
use ArrayAccess;
use InvalidArgumentException;
use Foil\Contracts\AliasAllowedTemplateInterface as Aliasable;

/**
 * Factory and holds templates object instances.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Factory
{
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
     * @var \Foil\Section\Factory
     */
    private $sections;

    /**
     * @var \Foil\Kernel\Command
     */
    private $command;

    /**
     * @var string
     */
    private $defaultClass;

    /**
     * @var string
     */
    private $alias;

    /**
     * @param \ArrayAccess          $templates
     * @param \Foil\Section\Factory $sections
     * @param \Foil\Kernel\Command  $command
     * @param array                 $options
     * @param null|string           $contract
     */
    public function __construct(
        ArrayAccess $templates,
        SectionFactory $sections,
        Command $command,
        array $options,
        $contract = null
    ) {
        $this->templates = $templates;
        $this->sections = $sections;
        $this->command = $command;
        $class = isset($options['template_class']) ? $options['template_class'] : '';
        $this->defaultClass = $class && class_exists($class) ? $class : self::DEFAULT_CLASS;
        $this->contract = is_string($contract) && interface_exists($contract)
            ? $contract
            : self::DEFAULT_CONTRACT;
        isset($options['alias']) and $this->alias = new Alias($options['alias']);
    }

    /**
     * Factory and/or returns template objects.
     *
     * @param  string                            $path      Full path to template file
     * @param  \Foil\Engine                      $engine
     * @param  string                            $className A custom template class name
     * @return \Foil\Contracts\TemplateInterface
     */
    public function factory($path, Engine $engine, $className = null)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException('Template path must be in a string.');
        }
        if (! $this->templates->offsetExists($path)) {
            $class = $this->getClass($className);
            $template = new $class($path, $this->sections, $engine, $this->command);
            ($template instanceof Aliasable && $this->alias) and $template->alias($this->alias);
            $this->templates[$path] = $template;
        }

        return $this->templates[$path];
    }

    /**
     * Checks that a given class name implements factory contract.
     *
     * @param  string $class
     * @return string
     * @access private
     */
    public function getClass($class)
    {
        if (is_string($class) && class_exists($class) && is_subclass_of($class, $this->contract)) {
            return $class;
        }

        return $this->defaultClass;
    }
}
