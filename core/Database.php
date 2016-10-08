<?php

namespace Neutron;

use PDO;

class Database
{
    /**
     * Check if class already has an instance.
     *
     * @var null
     */
    protected static $instance = null;

    public static $query;

    /**
     * constructor.
     */
    public function __construct()
    {
    }

    /**
     * cloning.
     */
    public function __clone()
    {
    }

    /**
     * Instantiate the Class.
     *
     * @return object
     */
    public static function instance()
    {
        if (self::$instance === null) {
            $args = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            );
            $config = 'mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME').';charset='.getenv('DB_CHAR');
            self::$instance = new PDO($config, getenv('DB_USER'), getenv('DB_PASS'), $args);
        }

        return self::$instance;
    }

    /**
     * Instantiate the class statically.
     *
     * @param  string
     * @param  array
     *
     * @return instance
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    /**
     * Perform a PDO Database Query.
     *
     * @param  string
     *
     * @return array
     */
    public static function query($query)
    {
        $statement = static::instance()->prepare($query);
        $statement->execute();

        return $statement->fetchAll();
    }
}
