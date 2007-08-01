<?php

$baseClasses = dirname(__FILE__) . '/classes/';

// Load the schema classes
include($baseClasses . 'schema/load.inc.php');

// Load the cough generation classes
include($baseClasses . 'CoughClass.class.php');
include($baseClasses . 'CoughConfig.class.php');
include($baseClasses . 'CoughGenerator.class.php');
include($baseClasses . 'CoughGeneratorConfig.class.php');
include($baseClasses . 'SchemaGenerator.class.php');
include($baseClasses . 'CoughWriter.class.php');

// Load the database schema generator classes
include($baseClasses . 'DatabaseSchemaGeneratorConfig.class.php');
include($baseClasses . 'DatabaseSchemaGenerator.class.php');

?>