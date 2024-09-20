<?php

namespace Neutron\Command;

use PDO;
use Neutron\Database\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure(): void
    {
        $this->setDescription('Run the database migrations.')
             ->setHelp('This command applies all pending database migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pdo = Connection::getPDO();
        $migrationPath = __DIR__ . '/../../migrations/';
        $logFile = __DIR__ . '/../../logs/migrations.log';

        // Create the logs directory if it doesn't exist
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        // Open the log file for appending
        $logHandle = fopen($logFile, 'a');

        $migrations = glob($migrationPath . '*.sql');
        foreach ($migrations as $migration) {
            $sql = file_get_contents($migration);

            // Execute the migration
            try {
                $pdo->exec($sql);
                $message = 'Executed migration: ' . basename($migration);
                $output->writeln($message);

                // Log the migration execution
                fwrite($logHandle, '[' . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL);
            } catch (\Exception $e) {
                $output->writeln('<error>Error executing migration: ' . basename($migration) . '</error>');
                fwrite($logHandle, '[' . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . PHP_EOL);
                fclose($logHandle);

                return Command::FAILURE;
            }
        }

        fclose($logHandle);
        return Command::SUCCESS;
    }
}
