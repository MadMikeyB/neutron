<?php

namespace Neutron;

class Installer
{
    public static function postCreateProject(): void
    {
        // Copy .env.example to .env
        if (!file_exists(__DIR__ . '/../.env')) {
            copy(__DIR__ . '/../.env.example', __DIR__ . '/../.env');
            echo ".env file has been created.\n";
        }

        // Ensure logs directory exists
        if (!file_exists(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0755, true);
            echo "Logs directory has been created.\n";
        }

        // Create the SQLite database file if using SQLite
        $env = parse_ini_file(__DIR__ . '/../.env');
        if (isset($env['DB_CONNECTION']) && $env['DB_CONNECTION'] === 'sqlite') {
            $databasePath = __DIR__ . '/../' . $env['DB_DATABASE'];
            if (!file_exists($databasePath)) {
                touch($databasePath);
                echo "SQLite database file has been created at: {$databasePath}\n";
            }
        }

        echo "Neutron installation complete.\n";
    }
}