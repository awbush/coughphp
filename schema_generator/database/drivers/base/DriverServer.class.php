<?php

/**
 * Defines the interface for a Server Driver.
 *
 * @package schema_generator
 * @author Anthony Bush
 **/
interface DriverServer {
	
	/**
	 * Load all database schemas for the currently connected server into memory.
	 * (will only load databases that the user/pass has privileges to see)
	 *
	 * @return void
	 **/
	public function loadDatabases();
	
	/**
	 * Load a specific database schema into memory.
	 * 
	 * @param $dbName - specific database name to load.
	 * @return void
	 **/
	public function loadDatabase($dbName);
	
	/**
	 * Get a list of available database names.
	 *
	 * @return array of strings
	 **/
	public function getAvailableDatabaseNames();
	
}

?>