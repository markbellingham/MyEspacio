<?php

declare(strict_types=1);

// Disable coding standard warnings
// phpcs:disable
// ROOT_DIR → project root regardless of folder name
define("ROOT_DIR", dirname(__DIR__, 1));

define('CONFIG', require ROOT_DIR . '/config/config.php');

// Autoload dependencies
require ROOT_DIR . '/vendor/autoload.php';

// phpcs:enable

// Set Xdebug trigger for debugging
putenv('XDEBUG_TRIGGER=1');
$_ENV['XDEBUG_TRIGGER'] = '1';
$_SERVER['XDEBUG_TRIGGER'] = '1';
