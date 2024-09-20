<?php

use Neutron\Command\HelloCommand;
use Neutron\Command\MigrateCommand;
use Symfony\Component\Console\Application;
use Neutron\Command\GenerateMigrationCommand;

/**
 * Register console commands here.
 *
 * @param Application $application The console application instance.
 */

$application->add(new HelloCommand());
$application->add(new MigrateCommand());
$application->add(new GenerateMigrationCommand());

// Add more commands here as needed
