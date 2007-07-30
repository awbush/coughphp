<?php

include_once('load.inc.php');

$server = new MysqlServer('127.0.0.1', 'nobody', '');
$server->loadDatabase('mediapc');

$databases = $server->getDatabases();
if (isset($databases['information_schema'])) {
	unset($databases['information_schema']);
}
// print_r($databases);

include_once('CoughSchemaGenerator.class.php');

$schemaGenerator = new CoughSchemaGenerator();
$schemaGenerator->loadDatabase($server->getDatabase('mediapc'));
$schemas  = $schemaGenerator->generateSchemas();

print_r($schemas);

?>