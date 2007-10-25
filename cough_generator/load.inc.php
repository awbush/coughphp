<?php

$basePath = dirname(dirname(__FILE__)) . '/';

// Load the core schema classes
include_once($basePath . 'schema/load.inc.php');

// Load the core cough generation classes
include('CoughClass.class.php');
include('CoughConfig.class.php');
include('CoughGenerator.class.php');
include('CoughGeneratorConfig.class.php');
include('CoughGeneratorFacade.class.php');
include('CoughWriter.class.php');

// Load the core schema generation classes
include($basePath . 'schema_generator/SchemaGenerator.class.php');

// Load the database schema generator classes (TODO: Make this decision one made by configuration files... i.e. only load the generator to use)
include($basePath . 'schema_generator/database/DatabaseSchemaGeneratorConfig.class.php');
include($basePath . 'schema_generator/database/DatabaseSchemaGenerator.class.php');

?>