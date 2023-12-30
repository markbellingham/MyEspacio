<?php

// tests/bootstrap.php
declare(strict_types=1);

const ROOT_DIR = __DIR__ . '/..';
define('CONFIG', require ROOT_DIR . '/config/config.php');
require ROOT_DIR . '/vendor/autoload.php';
