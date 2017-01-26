<?php

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('America/Bogota');

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/composer.lock')) {
    die("Dependencies must be installed using composer:"
        . "\n\nphp composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

// Include the composer autoloader
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
