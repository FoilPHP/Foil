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
use Aura\Html\HelperLocator;
use Aura\Html\Exception\HelperNotFound;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 */
class AuraHtml implements ExtensionInterface
{
    private static $tags = [
        'anchor',
        'anchorRaw',
        'base',
        'form',
        'img',
        'label',
        'links',
        'metas',
        'ol',
        'scripts',
        'styles',
        'tag',
        'title',
        'ul',
    ];

    /**
     * @var \Aura\Html\HelperLocator
     */
    private $locator;

    /**
     * @var bool
     */
    private $register;

    /**
     * @param \Aura\Html\HelperLocator $locator
     * @param bool                     $register_tag_functions
     */
    public function __construct(HelperLocator $locator, $register_tag_functions = false)
    {
        $this->locator = $locator;
        $this->register = filter_var($register_tag_functions, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @inheritdoc
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
        $base = [
            'html'  => [$this, 'html'],
            'input' => [$this, 'input'],
        ];

        return $this->register
            ? array_merge($base, array_combine(self::$tags, $this->tagCallbacks()))
            : $base;
    }

    /**
     * @return mixed
     * @throws \Aura\Html\Exception\HelperNotFound
     */
    public function html()
    {
        $args = func_get_args();
        $tag = $args ? array_shift($args) : '';
        if ($this->locator->has($tag)) {
            /** @var callable $helper */
            $helper = $this->locator->get($tag);

            return call_user_func_array($helper, $args);
        }

        throw new HelperNotFound();
    }

    /**
     * @param  string                              $type
     * @return string
     * @throws \Aura\Html\Exception\HelperNotFound
     */
    public function input($type)
    {
        $args = func_get_args();
        array_shift($args);
        /** @var \Aura\Html\Helper\Input $inputs */
        $inputs = $this->locator->get('input');
        if ($inputs->has($type)) {
            /** @var callable $helper */
            $helper = $this->locator->get($type);

            return call_user_func_array($helper, $args);
        }

        throw new HelperNotFound();
    }

    /**
     * @return array
     */
    private function tagCallbacks()
    {
        return array_map(function ($tag) {
            return function () use ($tag) {
                $args = func_get_args();
                array_unshift($args, $tag);

                return call_user_func_array([$this, 'html'], $args);
            };
        }, self::$tags);
    }

    /**
     * @inheritdoc
     */
    public function setup(array $args = [])
    {
        return;
    }
}
