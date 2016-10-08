<?php

namespace Neutron;

use Neutron\Database as DB;

abstract class Model {

	/**
	 * Check if class already has an instance
	 * @var null
	 */
    protected static $instance = null;

    /**
	 * Database Table to use
	 * @var string
	 */
	protected static $table = null;
    
    /**
	 * Primary Key
	 * @var string
	 */
	protected static $key = 'id';

    /**
     * constructor
     * 
     * @return  null
     */
	public function __construct() {}

    /**
     * Retreive all records from the table
     *
     * @return array 
     */
	public static function all()
	{
		return DB::query('SELECT * FROM '. static::$table);
	}

    /**
     * Retreive first record from the table
     *
     * @return array 
     */
	public static function first()
	{
		return DB::query('SELECT FIRST(' . static::$key . ') FROM '. static::$table );
	}

}