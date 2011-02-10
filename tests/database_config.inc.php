<?php

/**
 * Global/shared DSN config for all tests
 * 
 * The following SQL should get your DB server setup to run tests:
 * 
 * <code>
 * CREATE DATABASE test_cough_object;
 * GRANT USAGE ON `test_cough_object`.* TO `cough_test`@`localhost` IDENTIFIED BY 'cough_test';
 * CREATE DATABASE reports;
 * GRANT USAGE ON `reports`.* TO `cough_test`@`localhost` IDENTIFIED BY 'cough_test';
 * CREATE DATABASE actual_db_name;
 * GRANT USAGE ON `actual_db_name`.* TO `cough_test`@`localhost` IDENTIFIED BY 'cough_test';
 * CREATE DATABASE testConfigAlias;
 * GRANT USAGE ON `testConfigAlias`.* TO `cough_test`@`localhost` IDENTIFIED BY 'cough_test';
 * FLUSH PRIVILEGES;
 * </code>
 * 
 * @var array
 **/
$dsn = array(
	'host' => 'localhost',
	'user' => 'cough_test',
	'pass' => 'cough_test',
	'port' => null,
	'socket' => '/tmp/mysql.sock',
	'driver' => 'mysqli',
	'db_name' => 'test_cough_object',
);
// $dsn = array(
// 	'host' => 'localhost',
// 	'user' => 'cough_test',
// 	'pass' => 'cough_test',
// 	'port' => ':/tmp/mysql.sock',
// 	'driver' => 'mysql',
// 	'db_name' => 'test_cough_object',
// );

?>