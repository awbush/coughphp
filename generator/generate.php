<?php

// Include the Cough generation classes
include_once(dirname(__FILE__) . '/load.inc.php');

// Setup some paths
define('APP_PATH', dirname(__FILE__) . '/');
define('CONFIG_PATH', APP_PATH . 'config/');
define('CLASS_PATH', APP_PATH . 'classes/');

// Which config to use?
$configName = 'default';

// Database Schema Generator example

// Get the database config
include(CONFIG_PATH . $configName . '/database_schema_generator_config.php');
$schemaGeneratorConfig = new DatabaseSchemaGeneratorConfig($config);

// Get the cough generator config
include(CONFIG_PATH . $configName . '/cough_generator_config.php');
$coughGeneratorConfig = new CoughGeneratorConfig($config);

// Load the schema into memory
$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
$schema = $schemaGenerator->generateSchema();





?>