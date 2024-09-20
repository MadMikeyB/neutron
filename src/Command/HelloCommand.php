<?php
namespace Neutron\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    protected static $defaultName = 'hello';

    protected function configure(): void
    {
        $this->setDescription('Hello Command.')
             ->setHelp('This is an example command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello, World.');
        
        // Your logic goes here...

        return Command::SUCCESS;
    }
}