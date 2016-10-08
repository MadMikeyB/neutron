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
		return DB::query('SELECT * FROM ' . static::$table . ' ORDER BY ' . static::$key . ' ASC LIMIT 1');
	}

    /**
     * Retreive last record from the table
     *
     * @return array 
     */
	public static function last()
	{
		return DB::query('SELECT * FROM ' . static::$table . ' ORDER BY ' . static::$key . ' DESC LIMIT 1');
	}

    /**
     * Retreive last record from the table
     *
     * @return array 
     */
	public static function find($id)
	{
		if ( is_array( $id ) )
		{
			// return new Collection();
		}
		else
		{
			return DB::query('SELECT * FROM ' . static::$table . ' WHERE ' . static::$key . ' = '. $id );
		}
	}

}