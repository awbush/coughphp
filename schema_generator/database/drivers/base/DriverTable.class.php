<?php

/**
 * Defines the interface for a Table Driver, which should:
 * 
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package schema_generator
 * @author Anthony Bush
 **/
interface DriverTable {
	
	public function loadColumns();
	
}

?>