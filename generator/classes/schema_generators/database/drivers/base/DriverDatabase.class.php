<?php

interface DriverDatabase {
	
	public function loadTable($tableName);
	
	public function getAvailableTableNames();
	
	public function selectDb($dbName);
	
}

?>