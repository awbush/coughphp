<?php

interface DriverDatabase {
	
	abstract public function loadTable($tableName);
	
	abstract public function getAvailableTableNames();
	
	abstract public function selectDb($dbName);
	
}

?>