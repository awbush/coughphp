<?php

$config = array(
	// REQUIRED CONFIG

	// All databases will be scanned unless specified in the 'databases' parameter in the OPTIONAL CONFIG SECTION.
	'dsn' => array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'driver' => 'mysql'
	),

	// OPTIONAL ADDITIONAL CONFIG
	
	'database_settings' => array(
		'include_databases_matching_regex' => '/.*/',
		'exclude_databases_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
	),
	
	// You can override table_settings only on a per-database level and not a per-table level (b/c it doesn't make sense to)
	'table_settings' => array(
		// This match setting is so the database scanner can resolve
		// relationships better, e.g. know that when it sees "ticket_id"
		// that a "wfl_ticket" table is an acceptable match.
		'match_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
		
		'include_tables_matching_regex' => '/.*/',
		'exclude_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
	),

	'field_settings' => array(
		// In case of non FK detection, you can have the Database Schema Generator check for ID columns matching this regex.
		// This is useful, for example, when no FK relationships set up)
		'id_to_table_regex' => '/^(.*)_id$/',
	),
	
	'acceptable_join_databases' => 'all',

	// Now, we can override the global config on a per database level.
	// 'databases' => array(
	// 	'customer' => array(
	// 		'table_settings' => array(
	// 			'match_table_name_prefixes' => array('cust_'),
	// 		),
	// 
	// 		// Furthermore, we can override the table level settings
	// 		'tables' => array(
	// 			'table_name' => array(
	// 				'field_settings' => array(
	// 					'id_to_table_regex' => '/^(.*)_id$/',
	// 				),
	// 			),
	// 		),
	// 	),
	// ),
);

?>