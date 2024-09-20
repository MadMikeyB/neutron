<?php

namespace Neutron\Database;

use PDO;
use Neutron\Database\Connection;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    /**
     * Save the current model to the database.
     * If the model has a primary key, it will perform an update, otherwise an insert.
     */
    public function save(): void
    {
        if (isset($this->{static::$primaryKey})) {
            // Update existing record
            $this->update();
        } else {
            // Insert new record
            $this->insert();
        }
    }

    /**
     * Insert a new record into the database.
     */
    protected function insert(): void
    {
        $pdo = Connection::getPDO();

        // Get the column names and values
        $columns = array_keys(get_object_vars($this));
        $placeholders = array_map(fn ($col) => ":$col", $columns);
        $values = get_object_vars($this);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        // Set the primary key value after insert (for auto-incrementing keys)
        $this->{static::$primaryKey} = $pdo->lastInsertId();
    }

    /**
     * Update the existing record in the database.
     */
    protected function update(): void
    {
        $pdo = Connection::getPDO();

        // Get the column names and values
        $columns = array_keys(get_object_vars($this));
        $placeholders = array_map(fn ($col) => "$col = :$col", $columns);
        $values = get_object_vars($this);

        // Ensure the primary key is not updated
        $primaryKey = static::$primaryKey;
        unset($values[$primaryKey]);

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :%s",
            static::$table,
            implode(', ', $placeholders),
            $primaryKey,
            $primaryKey
        );

        // Bind the primary key value
        $values[$primaryKey] = $this->{$primaryKey};

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }

    /**
     * Delete the current model from the database.
     */
    public function delete(): void
    {
        $pdo = Connection::getPDO();

        $primaryKey = static::$primaryKey;
        $sql = sprintf(
            "DELETE FROM %s WHERE %s = :%s",
            static::$table,
            $primaryKey,
            $primaryKey
        );

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$primaryKey => $this->{$primaryKey}]);

        // Unset the primary key after deletion
        unset($this->{$primaryKey});
    }

    /**
     * Find a record by its primary key.
     */
    public static function find($id): ?self
    {
        $pdo = Connection::getPDO();
        $sql = sprintf("SELECT * FROM %s WHERE %s = :id", static::$table, static::$primaryKey);
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $instance = new static();
            foreach ($result as $column => $value) {
                $instance->{$column} = $value;
            }
            return $instance;
        }

        return null;
    }

    /**
     * Get all records from the table.
     */
    public static function all(): array
    {
        $pdo = Connection::getPDO();
        $sql = sprintf("SELECT * FROM %s", static::$table);
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    /**
     * Filter records with a where condition.
     */
    public static function where(string $column, string $operator, $value): array
    {
        $pdo = Connection::getPDO();
        $sql = sprintf("SELECT * FROM %s WHERE %s %s :value", static::$table, $column, $operator);
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }
}
