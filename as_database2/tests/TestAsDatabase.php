<?php
die('not finished -- switch from simpletest to phpunit and write real tests for as_database2. Also move this into the global tests directory.');

include(dirname(dirname(__FILE__)) . '/load.inc.php');

$logger = new As_DatabaseQueryLogger();

$db = As_Database::constructByConfig(array());
$db->addObserver($logger);
$result = $db->query('SHOW DATABASES');

echo "Databases:\n";
while ($row = $result->getRow())
{
	echo "\t" . $row['Database'] . "\n";
}

echo "\nQuery Log:\n";
print_r($logger->getQueryLog());

?>