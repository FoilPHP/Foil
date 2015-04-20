<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$autoload_path = dirname(dirname(__FILE__)).'/vendor/autoload.php';
if (! file_exists($autoload_path)) {
    die('Please install via composer before running tests.');
}
require_once $autoload_path;

$helpers_path = dirname($autoload_path).'/phpunit/phpunit/src/Framework/Assert/Functions.php';
if (! file_exists($helpers_path)) {
    die('Please install via composer with dev option before running tests.');
}
require_once $helpers_path;

if (! defined('FOILTESTSBASEPATH')) {
    define('FOILTESTSBASEPATH', __DIR__);
}
