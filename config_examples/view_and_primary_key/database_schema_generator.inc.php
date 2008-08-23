<?php
/**
 * Lightweight config example that generates for every database visible as "nobody" at localhost.
 **/

$config = array(
	'dsn' => array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'driver' => 'mysql'
	),
	
	'database_settings' => array(
		'include_databases_matching_regex' => '/.*/',
		'exclude_databases_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
	),
	
	// To get a view to generate, just specify the "primary_key" setting for the
	// table.  It can be a string or an array if the key makes up multiple columns.
	// This also works for tables that don't have a primary key in the database.
	'databases' => array(
		'my_database' => array(
			'tables' => array(
				'my_view' => array(
					'primary_key' => array('column_one', 'column_two'),
				),
			),
		),
	),
);

?>
