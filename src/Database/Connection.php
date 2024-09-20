<?php

namespace Neutron\Database;

use PDO;
use PDOException;
use League\Container\Container;

class Connection
{
    protected static PDO $pdo;
    protected static Container $container;

    /**
     * Set the container (called during setup).
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Get the PDO connection.
     *
     * @return PDO
     */
    public static function getPDO(): PDO
    {
        if (!isset(self::$pdo)) {
            self::$pdo = self::makeConnection();
        }

        return self::$pdo;
    }

    /**
     * Make a database connection using environment variables from the container.
     *
     * @return PDO
     */
    protected static function makeConnection(): PDO
    {
        if (!isset(self::$container)) {
            throw new PDOException("Container not set for database connection.");
        }

        // Get environment variables from the container
        $env = self::$container->get('env');
        $connection = $env['db_connection'];
        $database = $env['db_database'];
        $username = $env['db_username'];
        $password = $env['db_password'];

        try {
            switch ($connection) {
                case 'mysql':
                    $dsn = "mysql:host=localhost;dbname={$database}";
                    return new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                case 'pgsql':
                    $dsn = "pgsql:host=localhost;dbname={$database}";
                    return new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                case 'sqlite':
                    self::ensureSqliteDatabaseExists($database);
                    $dsn = "sqlite:{$database}";
                    return new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                default:
                    throw new PDOException("Unsupported database type: {$connection}");
            }
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the SQLite database file exists, create it if not.
     *
     * @param string $databasePath
     */
    protected static function ensureSqliteDatabaseExists(string $databasePath): void
    {
        // Ensure the directory exists
        $directory = dirname($databasePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create the database file if it doesn't exist
        if (!file_exists($databasePath)) {
            file_put_contents($databasePath, '');
        }
    }
}
