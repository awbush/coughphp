<?php

$baseClasses = dirname(__FILE__) . '/';

// Load the core schema classes
include($baseClasses . 'schema/SchemaColumn.class.php');
include($baseClasses . 'schema/SchemaDatabase.class.php');
include($baseClasses . 'schema/Schema.class.php');
include($baseClasses . 'schema/SchemaTable.class.php');

// Load the core cough generation classes
include($baseClasses . 'CoughClass.class.php');
include($baseClasses . 'CoughConfig.class.php');
include($baseClasses . 'CoughGenerator.class.php');
include($baseClasses . 'CoughGeneratorConfig.class.php');
include($baseClasses . 'CoughWriter.class.php');

// Load the core schema generation classes
include($baseClasses . 'schema_generators/SchemaGenerator.class.php');

// Load the database schema generator classes (TODO: Make this decision one made by configuration files... i.e. only load the generator to use)
include($baseClasses . 'schema_generators/database/DatabaseSchemaGeneratorConfig.class.php');
include($baseClasses . 'schema_generators/database/DatabaseSchemaGenerator.class.php');

?>