<?php
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
