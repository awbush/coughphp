<?php
/**
 * Default DatabaseSchemaGenerator configuration options.
 * 
 * You don't have to pass these to the schema generator as it will use
 * reasonable defaults. The are replicated here to make them easy to change.
 *
 * @package tests
 * @author Anthony Bush
 **/

require(dirname(dirname(dirname(__FILE__))) . '/database_config.inc.php');

$config = array(
	'dsn' => $dsn,
	'database_settings' => array(
		'include_databases_matching_regex' => '/^(' . $dsn['db_name'] . ')$/',
	),
);

?>
