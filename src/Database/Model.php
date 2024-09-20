<?php

namespace Neutron\Database;

use PDO;
use Neutron\Database\Connection;

/**
 * Abstract Model class for interacting with the database.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    // Constants to represent the query types
    protected const QUERY_SELECT = 'select';
    protected const QUERY_INSERT = 'insert';
    protected const QUERY_UPDATE = 'update';
    protected const QUERY_DELETE = 'delete';

    protected string $queryType = self::QUERY_SELECT; // Default to 'select'

    // Internal properties to build the query
    protected array $conditions = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?string $orderBy = null;
    protected ?string $orderDirection = 'ASC';
    protected array $internalProperties = ['internalProperties', 'queryType', 'conditions', 'limit', 'offset', 'orderBy', 'orderDirection'];

    /**
     * Initialize a new instance for chaining query building.
     *
     * @return static
     */
    public static function query(): static
    {
        return new static();
    }

    /**
     * Filter records with a where condition.
     *
     * @param string $column   The column to filter by.
     * @param string $operator The operator for the filter (e.g., '=', '>', '<').
     * @param mixed  $value    The value to filter with.
     * @return static
     */
    public function where(string $column, string $operator, $value): static
    {
        $this->conditions[] = [$column, $operator, $value];
        return $this;
    }

    /**
     * Set a limit on the number of records to return.
     *
     * @param int $limit The maximum number of records to return.
     * @return static
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set an offset for the records to return.
     *
     * @param int $offset The offset to apply to the records.
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Set an order by clause for the query.
     *
     * @param string $column The column to order by.
     * @param string $direction The direction to order (ASC or DESC).
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy = $column;
        $this->orderDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        return $this;
    }

    /**
     * Get the first record that matches the conditions.
     *
     * @return static|null
     */
    public function one(): ?static
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Get all records that match the conditions.
     *
     * @return static[]
     */
    public function all(): array
    {
        return $this->get();
    }

    /**
     * Execute the query and return the result set.
     *
     * @return static[]
     */
    public function get(): array
    {
        $pdo = Connection::getPDO();

        // Build the WHERE clause
        $sql = sprintf("SELECT * FROM %s", static::$table);
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_map(fn($cond) => "$cond[0] $cond[1] :{$cond[0]}", $this->conditions));
        }

        // Add ORDER BY clause if specified
        if ($this->orderBy !== null) {
            $sql .= " ORDER BY {$this->orderBy} {$this->orderDirection}";
        }

        // Add LIMIT and OFFSET if applicable
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        // Prepare and bind values
        $stmt = $pdo->prepare($sql);
        foreach ($this->conditions as [$column, , $value]) {
            $stmt->bindValue(":$column", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    /**
     * Check if any records exist that match the conditions.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $pdo = Connection::getPDO();

        // Build the WHERE clause
        $sql = sprintf("SELECT 1 FROM %s", static::$table);
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_map(fn($cond) => "$cond[0] $cond[1] :{$cond[0]}", $this->conditions));
        }

        $sql .= " LIMIT 1";

        // Prepare and bind values
        $stmt = $pdo->prepare($sql);
        foreach ($this->conditions as [$column, , $value]) {
            $stmt->bindValue(":$column", $value);
        }

        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    // Other methods like save, update, delete...

    /**
     * Save the current model to the database.
     */
    public function save(): void
    {
        if (isset($this->{static::$primaryKey})) {
            // Set query type and update existing record
            $this->queryType = self::QUERY_UPDATE;
            $this->update();
        } else {
            // Set query type and insert new record
            $this->queryType = self::QUERY_INSERT;
            $this->insert();
        }
    }

    /**
     * Insert a new record into the database.
     */
    protected function insert(): void
    {
        $pdo = Connection::getPDO();
    
        // Get only the model properties, excluding internal properties
        $modelVars = array_diff_key(get_object_vars($this), array_flip($this->internalProperties));
        $columns = array_keys($modelVars);
        $placeholders = array_map(fn($col) => ":$col", $columns);
    
        // Prepare the SQL statement
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
    
        // Prepare and execute the statement
        $stmt = $pdo->prepare($sql);
        $stmt->execute($modelVars);
    
        // Set the primary key value after insert (for auto-incrementing keys)
        $this->{static::$primaryKey} = $pdo->lastInsertId();
    }

    /**
     * Update the existing record in the database.
     */
    protected function update(): void
    {
        $pdo = Connection::getPDO();

        // Get only the model properties, excluding internal properties
        $modelVars = array_diff_key(get_object_vars($this), array_flip($this->internalProperties));

        // Ensure the primary key is not updated
        $primaryKey = static::$primaryKey;
        unset($modelVars[$primaryKey]);

        // Prepare the columns for the SQL update statement
        $columns = array_keys($modelVars);
        $placeholders = array_map(fn($col) => "$col = :$col", $columns);

        // Prepare the SQL statement
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :%s",
            static::$table,
            implode(', ', $placeholders),
            $primaryKey,
            $primaryKey
        );

        // Add the primary key value to the parameters
        $modelVars[$primaryKey] = $this->{$primaryKey};

        // Prepare and execute the statement
        $stmt = $pdo->prepare($sql);
        $stmt->execute($modelVars);
    }

    /**
     * Delete the current model from the database.
     */
    public function delete(): void
    {
        // Set query type to delete
        $this->queryType = self::QUERY_DELETE;

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
        unset($this->{$primaryKey});
    }

    /**
     * Find a record by its primary key.
     *
     * @param mixed $id The value of the primary key.
     * @return static|null The model instance or null if not found.
     */
    public static function find($id): ?static
    {
        return static::query()->where(static::$primaryKey, '=', $id)->one();
    }

    /**
     * Build and return the SQL query as a string based on the query type.
     *
     * @return string
     */
    public function toSql(): string
    {
        switch ($this->queryType) {
            case self::QUERY_INSERT:
                $columns = array_keys(get_object_vars($this));
                $placeholders = array_map(fn ($col) => ":$col", $columns);
                return sprintf(
                    "INSERT INTO %s (%s) VALUES (%s)",
                    static::$table,
                    implode(', ', $columns),
                    implode(', ', $placeholders)
                );

            case self::QUERY_UPDATE:
                $columns = array_keys(get_object_vars($this));
                $placeholders = array_map(fn ($col) => "$col = :$col", $columns);
                return sprintf(
                    "UPDATE %s SET %s WHERE %s = :%s",
                    static::$table,
                    implode(', ', $placeholders),
                    static::$primaryKey,
                    static::$primaryKey
                );

            case self::QUERY_DELETE:
                return sprintf(
                    "DELETE FROM %s WHERE %s = :%s",
                    static::$table,
                    static::$primaryKey,
                    static::$primaryKey
                );

            case self::QUERY_SELECT:
            default:
                $sql = sprintf("SELECT * FROM %s", static::$table);
                if (!empty($this->conditions)) {
                    $sql .= " WHERE " . implode(' AND ', array_map(fn($cond) => "$cond[0] $cond[1] :{$cond[0]}", $this->conditions));
                }

                if ($this->orderBy !== null) {
                    $sql .= " ORDER BY {$this->orderBy} {$this->orderDirection}";
                }

                if ($this->limit !== null) {
                    $sql .= " LIMIT " . $this->limit;
                }

                if ($this->offset !== null) {
                    $sql .= " OFFSET " . $this->offset;
                }

                return $sql;
        }
    }
}