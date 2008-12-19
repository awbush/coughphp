<?php

/**
 * Global/shared DSN config for all tests
 * 
 * The following SQL should get your DB server setup to run tests:
 * 
 * <code>
 * CREATE DATABASE test_cough_object;
 * GRANT USAGE ON `test_cough_object`.* TO `cough_test`@`localhost` IDENTIFIED BY 'cough_test';
 * FLUSH PRIVILEGES;
 * </code>
 * 
 * @var array
 **/
$dsn = array(
	'host' => 'localhost',
	'user' => 'cough_test',
	'pass' => 'cough_test',
	'port' => 3306,
	'driver' => 'mysql',
	'db_name' => 'test_cough_object',
);

?>