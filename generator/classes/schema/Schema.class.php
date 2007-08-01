<?php

class Schema {
	
	/**
	 * Stores the databases (SchemaDatabase) that have been loaded into
	 * memory so far.
	 *
	 * @var string
	 **/
	protected $databases = array();
	
	/**
	 * Returns the databases that were loaded from
	 * the server's DSN.
	 *
	 * @return array
	 **/
	public function getDatabases() {
		return $this->databases;
	}
	
	/**
	 * Get the specified database name, or null if it hasn't been set yet.
	 *
	 * @return mixed
	 * @author Anthony Bush
	 **/
	public function getDatabase($dbName) {
		if (isset($this->databases[$dbName])) {
			return $this->databases[$dbName];
		} else {
			return null;
		}
	}
	
	/**
	 * Add a database to the pile.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function addDatabase($database) {
		$this->databases[$databases->getDatabaseName()] = $databases;
	}
	
}

?>