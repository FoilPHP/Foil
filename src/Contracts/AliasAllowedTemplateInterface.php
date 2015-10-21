<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Contracts;

use Foil\Template\Alias;

/**
 * Core object of the package, is the front controller for all templates features.
 * Function in template files are executed in the context of this object:
 * in template files `$this` refers to a `TemplateInterface` object.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
interface AliasAllowedTemplateInterface extends TemplateInterface
{
    /**
     * @param  \Foil\Template\Alias                          $alias
     * @return \Foil\Contracts\AliasAllowedTemplateInterface
     */
    public function alias(Alias $alias);
}
