#!/usr/bin/env php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application('Neutron Console', '1.0.0');
    
// Load commands from the external console routes file
if (file_exists(__DIR__ . '/src/console.php')) {
    require __DIR__ . '/src/console.php';
}

// Auto-load commands from the `src/Command` directory
foreach (glob(__DIR__ . '/src/Command/*.php') as $commandFile) {
    $commandClass = basename($commandFile, '.php');
    $fullyQualifiedClassName = "Neutron\\Command\\{$commandClass}";
    if (class_exists($fullyQualifiedClassName)) {
        $application->add(new $fullyQualifiedClassName());
    }
}

$application->run();
