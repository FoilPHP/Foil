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

/**
 * Provides an interface to AuraPhp Html helpers.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Foil
 * @link    https://github.com/auraphp/Aura.Html
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
        'input',
    ];

    private static $inputs = [
        'button',
        'checkbox',
        'color',
        'date',
        'datetime',
        'datetime-local',
        'email',
        'file',
        'hidden',
        'image',
        'month',
        'number',
        'password',
        'radio',
        'range',
        'reset',
        'search',
        'select',
        'submit',
        'tel',
        'text',
        'textarea',
        'time',
        'url',
        'week',
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
     * @param bool                     $registerTagFunctions
     */
    public function __construct(HelperLocator $locator, $registerTagFunctions = false)
    {
        $this->locator = $locator;
        $this->register = filter_var($registerTagFunctions, FILTER_VALIDATE_BOOLEAN);
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
        $base = [
            'html' => [$this, 'html'],
        ];

        return $this->register
            ? array_merge($base, array_combine(self::$tags, $this->tagCallbacks()))
            : $base;
    }

    /**
     * @param  string                              $tag
     * @return mixed
     * @throws \Aura\Html\Exception\HelperNotFound
     * @link https://github.com/auraphp/Aura.Html/blob/2.x/README-HELPERS.md
     */
    public function html($tag)
    {
        $args = func_get_args();
        $tag = array_shift($args);
        if (in_array($tag, self::$inputs, true) && $args && is_array($args[0])) {
            return $this->input($tag, $args[0]);
        } elseif ($tag === 'input' && $args && is_array($args[0]) && isset($args[0]['type'])) {
            return $this->input($args[0]['type'], $args[0]);
        }
        /** @var callable $callable */
        $callable = $this->locator->get($tag);

        return call_user_func_array($callable, $args);
    }

    /**
     * @param  string                              $type
     * @param  array                               $args
     * @return string
     * @throws \Aura\Html\Exception\HelperNotFound
     * @link https://github.com/auraphp/Aura.Html/blob/2.x/README-FORMS.md
     */
    private function input($type, array $args)
    {
        $args['type'] = $type;
        /** @var callable $callable */
        $callable = $this->locator->get('input');
        $input = call_user_func($callable, $args);

        return $input->__toString();
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
     * @codeCoverageIgnore
     */
    public function setup(array $args = [])
    {
        return;
    }
}
