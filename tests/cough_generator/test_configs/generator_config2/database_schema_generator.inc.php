<?php

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/database_config.inc.php');

$config = array(
	'dsn' => $dsn,
	'database_settings' => array(
		'include_databases_matching_regex' => '/^(' . $dsn['db_name'] . ')$/',
	),
);

?>