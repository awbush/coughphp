
CoughGenerator takes SchemaDefinition and returns CodeGeneration

CoughGeneration



<?php

class DatabaseDefinition extends SchemaDefinition {
	
}


/**
 * 2007-05-11 awbush
 * 
 * Notes about the system... as we create it
 * 
 * 
 *     - All classes take configuration parameters in the constructor (or nothing).
 *     - A function call is used to get results. generateSchema(), generateCode(), writeCodeToDisk()
 * 
 * 2007-06-15 awbush
 * 
 *     - Classes will take a configuration object, not an array. They will make function calls to the object to ask about things, i.e. "shouldGenerateForDatabase($dbName)" and shouldGenerateForTable($tableName).
 *         - By default, it will use the regex stuff, but the abstraction is that (1) if the config format changes we need only update the config object and (2) allows the user to override the config object with one that includes custom logic (in case the config parameters do not supply enough options)
 **/


$schemaGenerator = new DatabaseSchemaGenerator($configArray);
$schema = $schemaGenerator->generateSchema();

$generator = new CoughGenerator(/* optional config? */);
$phpClasses = $generator->generatePhpCoughClasses($schema);

$phpClasses->setConfig($configArray); // will use path information for diffing and writing files to disk.
$phpClasses->getModifiedFiles();
$phpClasses->getRemovedFiles();
$phpClasses->getAddedFiles();


class PhpClassCollection {
	
}

class PhpClass {
	public $className = '';
	public $classDefinition = '';
}

class PhpCoughClass extends PhpClass {
	const COLLECTION_CLASS = 1;
	const OBJECT_CLASS = 2;
	public $classType = null;
	public $isGenerated = false;
}



?>