<?php

namespace Neutron\Database;

use PDO;

abstract class Model
{
    protected static string $table;

    /**
     * Get all rows from the table.
     *
     * @return array
     */
    public static function all(): array
    {
        $pdo = Connection::getPDO();
        $statement = $pdo->query("SELECT * FROM " . static::$table);
        return $statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get a single row from the table by ID.
     *
     * @param int $id
     * @return object|null
     */
    public static function one(int $id): ?object
    {
        $pdo = Connection::getPDO();
        $statement = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE id = :id LIMIT 1");
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Get rows from the table based on a condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    public static function where(string $column, string $operator, $value): array
    {
        $pdo = Connection::getPDO();
        $sql = "SELECT * FROM " . static::$table . " WHERE {$column} {$operator} :value";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':value', $value);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_OBJ);
    }
}
