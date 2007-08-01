<?php

/**
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package Schema
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
interface DriverTable {
	
	abstract public function loadColumns();
	
}

?>