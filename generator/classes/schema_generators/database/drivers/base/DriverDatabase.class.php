<?php

interface DriverDatabase {
	
	/**
	 * Load all tables for the database into memory.
	 * (will only load tables that the user/pass has privileges to see)
	 *
	 * @return void
	 **/
	public function loadTables();
	
	public function loadTable($tableName);
	
	public function getAvailableTableNames();
	
	public function selectDb($dbName);
	
}

?>