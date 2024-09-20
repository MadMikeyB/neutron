<?php

namespace Neutron\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigrationCommand extends Command
{
    protected static $defaultName = 'generateMigration';

    protected function configure(): void
    {
        $this->setDescription('Generate a new migration file and optionally a corresponding model.')
             ->setHelp('This command allows you to create a new database migration, with an optional model file.')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the migration (e.g., create_messages_table)')
             ->addOption('model', 'm', InputOption::VALUE_NONE, 'Generate a corresponding model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get the migration name from the argument
        $name = $input->getArgument('name');

        // Generate the migration
        $this->createMigration($name, $output);

        // Generate the model only if the -m option is provided
        if ($input->getOption('model')) {
            $this->createModel($name, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * Generate the migration file.
     */
    private function createMigration(string $name, OutputInterface $output): void
    {
        // Create the migrations directory if it doesn't exist
        $migrationDir = __DIR__ . '/../../migrations';
        if (!is_dir($migrationDir)) {
            mkdir($migrationDir, 0755, true);
        }

        // Generate the filename with a Unix timestamp and the user input
        $timestamp = time();
        $filename = "{$timestamp}_{$name}.sql";
        $filePath = "{$migrationDir}/{$filename}";

        // Create a stub SQL file
        $stub = "-- Migration: {$name}\n-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        file_put_contents($filePath, $stub);

        // Output the result
        $output->writeln("Migration created: {$filename}");
    }

    /**
     * Generate the model file based on the migration name.
     */
    private function createModel(string $migrationName, OutputInterface $output): void
    {
        // Convert the migration name into a model name (e.g., create_messages_table -> Message)
        [$modelName, $tableName] = $this->convertMigrationToNames($migrationName);

        // Ensure the model name is singular
        $modelName = $this->singularize($modelName);

        // Create the models directory if it doesn't exist
        $modelDir = __DIR__ . '/../../src/Models';
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }

        // Define the path for the new model file
        $modelPath = "{$modelDir}/{$modelName}.php";

        // Check if the model already exists
        if (file_exists($modelPath)) {
            $output->writeln("<error>Model {$modelName} already exists!</error>");
            return;
        }

        // Create the model file with a basic class definition
        $stub = "<?php\n\nnamespace Neutron\Models;\n\nuse Neutron\Database\Model;\n\nclass {$modelName} extends Model\n{\n    protected static string \$table = '{$tableName}';\n}\n";
        file_put_contents($modelPath, $stub);

        // Output the result
        $output->writeln("Model created: {$modelPath}");
    }

    /**
     * Convert a migration name like 'create_messages_table' or 'add_column_to_messages_table'
     * into a model name (PascalCase) and table name (snake_case).
     *
     * @param string $migrationName
     * @return array An array containing the model name and table name.
     */
    private function convertMigrationToNames(string $migrationName): array
    {
        // Split the migration name into parts using underscores
        $parts = explode('_', $migrationName);

        // Find the part that immediately precedes the word 'table'
        $tableIndex = array_search('table', $parts);

        // If 'table' is found and has a preceding word
        if ($tableIndex !== false && $tableIndex > 0) {
            // Extract the part before 'table' and use it as the table name
            $tableName = $parts[$tableIndex - 1];

            // Handle cases like 'add_column_to_messages_table' where 'to' is present
            if (($tableIndex - 2) >= 0 && $parts[$tableIndex - 2] === 'to') {
                $tableName = $parts[$tableIndex - 1];
            }

            // Convert the table name to snake_case (for DB) and PascalCase (for class)
            $modelName = ucfirst($this->convertToPascalCase($tableName));
            $tableName = strtolower($this->convertToSnakeCase($tableName));

            return [$modelName, $tableName]; // No extra 's' added here
        }

        // If the pattern doesn't match, fallback to generating unique names from the migration name
        return $this->generateFallbackNames($migrationName);
    }

    /**
     * Ensure the model name is singular.
     *
     * @param string $modelName
     * @return string
     */
    private function singularize(string $modelName): string
    {
        return rtrim($modelName, 's');  // Basic singularization logic
    }

    /**
     * Generate fallback model and table names from the migration name.
     *
     * This strips common keywords (create, delete, add, update) and uses the rest of the name.
     *
     * @param string $migrationName
     * @return array
     */
    private function generateFallbackNames(string $migrationName): array
    {
        // List of common action keywords to remove
        $keywordsToRemove = ['create', 'delete', 'add', 'update', 'column', 'to', 'table'];

        // Split the migration name into parts
        $parts = explode('_', $migrationName);

        // Remove the action keywords from the parts
        $filteredParts = array_filter($parts, function ($part) use ($keywordsToRemove) {
            return !in_array($part, $keywordsToRemove);
        });

        // If we have remaining parts, build names based on them
        if (!empty($filteredParts)) {
            $coreName = implode('_', $filteredParts);
            $modelName = $this->convertToPascalCase($coreName);
            $tableName = $this->convertToSnakeCase($coreName);

            return [$modelName, $tableName];  // No extra 's' here
        }

        // As a last fallback, use 'Model' and 'models'
        return ['Model', 'models'];
    }

    /**
     * Convert a string to PascalCase (e.g., 'messages' -> 'Message').
     *
     * @param string $string
     * @return string
     */
    private function convertToPascalCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Convert a string to snake_case (e.g., 'Message' -> 'message').
     *
     * @param string $string
     * @return string
     */
    private function convertToSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}
