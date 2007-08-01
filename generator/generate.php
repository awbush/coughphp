<?php

include_once(dirname(__FILE__) . '/load.inc.php');

// Database Schema Generator example

// Get the config
include(CONFIG_DIR . 'database_schema_generator.conf.php');
$schemaGeneratorConfig = new DatabaseSchemaGeneratorConfig($config);

// Get the generator
$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
$schemaGenerator->generateSchema();



?>