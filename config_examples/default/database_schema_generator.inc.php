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
);

?>
