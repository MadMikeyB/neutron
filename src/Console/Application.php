<?php

namespace Neutron\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        // Prefix the output with "Executing Command: {name}"
        $output->writeln(sprintf('<info>[Neutron] [%s] Executing Command: %s</info>', date('y-m-d H:i:s'), $command->getName()));
        $output->writeln('');

        // Now run the original command
        return parent::doRunCommand($command, $input, $output);
    }
}