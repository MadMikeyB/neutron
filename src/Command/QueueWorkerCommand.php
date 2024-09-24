<?php

namespace Neutron\Command;

use Neutron\Queue\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorkerCommand extends Command
{
    protected static $defaultName = 'queue:work';

    protected function configure(): void
    {
        $this->setDescription('Process jobs in the queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queue = new Queue();

        while (true) {
            // Pop the next job from the queue
            $job = $queue->pop();

            if ($job !== null) {
                // Output the processing message
                $output->writeln(sprintf('[Processing Job: %s]', get_class($job)));

                // Process the job
                $job->handle();
            } else {
                // Sleep for a bit before checking again
                sleep(2);
            }
        }

        return Command::SUCCESS;
    }
}