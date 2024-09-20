<?php

namespace Neutron\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Neutron\Database\Connection;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure(): void
    {
        $this->setDescription('Run database migrations.')
             ->setHelp('This command allows you to run pending database migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pdo = Connection::getPDO();

        // Ensure the migrations table exists
        $this->ensureMigrationsTableExists($pdo);

        // Get all migration files
        $migrationDir = __DIR__ . '/../../migrations';
        $migrationFiles = glob($migrationDir . '/*.sql');

        // Fetch already run migrations from the migrations table
        $runMigrations = $this->getRunMigrations($pdo);

        // Run each migration if it hasn't been run yet
        foreach ($migrationFiles as $file) {
            $filename = basename($file);

            // Skip if the migration has already been run
            if (in_array($filename, $runMigrations)) {
                $output->writeln("<info>Skipping already applied migration: {$filename}</info>");
                continue;
            }

            // Run the migration
            $output->writeln("<comment>Running migration: {$filename}</comment>");
            $this->runMigration($pdo, $file);

            // Record the migration as run
            $this->recordMigration($pdo, $filename);
            $output->writeln("<info>Migration applied: {$filename}</info>");
        }

        return Command::SUCCESS;
    }

    /**
     * Ensure the migrations table exists in the database.
     */
    private function ensureMigrationsTableExists(\PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ";
        $pdo->exec($sql);
    }

    /**
     * Get the list of already run migrations.
     */
    private function getRunMigrations(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Run a migration by executing the SQL in the file.
     */
    private function runMigration(\PDO $pdo, string $file): void
    {
        $sql = file_get_contents($file);
        $pdo->exec($sql);
    }

    /**
     * Record a migration as being run.
     */
    private function recordMigration(\PDO $pdo, string $filename): void
    {
        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
        $stmt->execute(['migration' => $filename]);
    }
}