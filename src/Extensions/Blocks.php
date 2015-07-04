<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foil\Extensions;

use Foil\Contracts\ExtensionInterface;
use Foil\Blocks\Blocks as BlocksBucket;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class Blocks implements ExtensionInterface
{
    /**
     * @var array
     */
    private $args;

    /**
     * @var \Foil\Blocks\Blocks
     */
    private $blocks;

    /**
     * @param \Foil\Blocks\Blocks $blocks
     */
    public function __construct(BlocksBucket $blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function setup(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function provideFilters()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function provideFunctions()
    {
        return [
            'block'    => [$this, 'open'],
            'endblock' => [$this, 'close']
        ];
    }

    /**
     * Open a block by name
     *
     * @param string $name
     */
    public function open($name)
    {
        $args = func_num_args() > 1 ? array_slice(func_get_args(), 1) : [];
        is_string($name) and call_user_func([$this->blocks, 'open'], $name, $args);
    }

    /**
     * Close last opened block.
     *
     * @param string|null $name
     */
    public function close($name = null)
    {
        echo $this->blocks->close($name);
    }
}
