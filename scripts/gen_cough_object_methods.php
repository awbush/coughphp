#!/usr/bin/env php
<?php

if (!isset($_SERVER['argv'])) {
	echo("Must run this from command line.\n");
	exit(1);
}

// Helpers

class As_String
{
	public static function titleCase($underscoredString) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $underscoredString)));
	}

	public static function underscore($camelCasedString) {
		return preg_replace('/_i_d$/', '_id', strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedString)));
	}
}

function print_usage_info()
{
	$scriptName = basename(__FILE__);
	echo("NAME
	$scriptName -- generate helper code for CoughPHP *_Object methods

SYNOPSIS
	$scriptName class_name entity_name

DESCRIPTION
	$scriptName helps in the creation of customized CoughPHP *_Object methods.
	
	The options are as follows:
	
	-h
	--help
	      Display this help information.

EXAMPLES
	$scriptName cnt_ProductWoot NextProductWoot
	$scriptName cnt_ProductWoot_Object NextProductWoot_Object
	      These all do the same thing, _Object is optional.
");
}

// Process command line arguments

$argc = $_SERVER['argc'];
$shouldGenerateAddRemove = false;
$classEntityArgs = array();

for ($i = 1; $i < $argc; ++$i)
{
	$arg = $_SERVER['argv'][$i];
	switch ($arg)
	{
		case '-h':
		case '--help':
			print_usage_info();
			exit();
		break;
		default:
			$classEntityArgs[] = $arg;
		break;
	}
}

if (count($classEntityArgs) != 2)
{
	print_usage_info();
	exit(1);
}

$className = str_replace('_Object', '', trim($classEntityArgs[0]));
$entityName = str_replace('_Object', '', trim($classEntityArgs[1]));
$entityTitleName = As_String::titleCase($entityName);

?>
	// Merge this into existing defineObjects() function if it already exists...
	protected function defineObjects()
	{
		parent::defineObjects();
		$this->objectDefinitions['<?php echo $entityTitleName ?>_Object'] = array('class_name' => '<?php echo $className ?>');
	}
	
	public function load<?php echo $entityTitleName ?>_Object()
	{
		if (!$this->hasKeyID())
		{
			return;
		}
		
		$db = self::getDb();
		$tableName = <?php echo $className ?>::getTableName();
		
		$sql = '
			SELECT
				*
			FROM
				`' . $tableName . '`
			#WHERE
			#...
			#LIMIT 1
        ';
        
		$this->set<?php echo $entityTitleName ?>_Object(<?php echo $className ?>::constructBySql($sql));
	}
	
	public function get<?php echo $entityTitleName ?>_Object()
	{
		if (!isset($this->objects['<?php echo $entityTitleName ?>_Object'])) {
			$this->load<?php echo $entityTitleName ?>_Object();
		}
		return $this->objects['<?php echo $entityTitleName ?>_Object'];
	}
	
	public function set<?php echo $entityTitleName ?>_Object($object)
	{
		$this->objects['<?php echo $entityTitleName ?>_Object'] = $object;
	}
