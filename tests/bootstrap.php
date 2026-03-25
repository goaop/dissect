<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die("Setup the project dependencies before running unit tests." . PHP_EOL);
}

$loader = require __DIR__ . '/../vendor/autoload.php';

if ($loader instanceof \Composer\Autoload\ClassLoader) {
    $loader->add('Dissect', __DIR__);
}
