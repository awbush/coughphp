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
	$scriptName -- generate helper code for CoughPHP *_Collection methods

SYNOPSIS
	$scriptName class_name entity_name [--addremove]

DESCRIPTION
	$scriptName helps in the creation of customized CoughPHP *_Collection methods.
	
	The options are as follows:
	
	--addremove
	      Also generate add*() and remove*() methods.
	
	-h
	--help
	      Display this help information.

EXAMPLES
	$scriptName cnt_ProductWoot ProductWoot
	$scriptName cnt_ProductWoot_Collection ProductWoot_Collection
	      These all do the same thing, _Collection is optional.
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
		case '--addremove':
			$shouldGenerateAddRemove = true;
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

$className = str_replace('_Collection', '', trim($classEntityArgs[0]));
$entityName = str_replace('_Collection', '', trim($classEntityArgs[1]));
$entityTitleName = As_String::titleCase($entityName);

?>
	public function load<?php echo $entityTitleName ?>_Collection()
	{
		// Always create the collection
		$collection = new <?php echo $className ?>_Collection();
		$this->set<?php echo $entityTitleName ?>_Collection($collection);
		
		// But only populate it if we have key ID
		if (!$this->hasKeyId()) {
			return;
		}
		
		$db = <?php echo $className ?>::getDb();
		$tableName = <?php echo $className ?>::getTableName();
		$sql = '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . <?php echo $className ?>::getDbName() . '`.`' . $tableName . '`
			#WHERE
			#	`' . $tableName . '`.`remote_field_id` = ' . $db->quote($this->getLocalFieldId()) . '
		';
		
		// Construct and populate the collection
		$collection->loadBySql($sql);
		//foreach ($collection as $element) {
		//	$element->setRemoteField_Object($this);
		//}
	}
	
	public function get<?php echo $entityTitleName ?>_Collection()
	{
		if (!isset($this->collections['<?php echo $entityTitleName ?>_Collection'])) {
			$this->load<?php echo $entityTitleName ?>_Collection();
		}
		return $this->collections['<?php echo $entityTitleName ?>_Collection'];
	}
	
	public function set<?php echo $entityTitleName ?>_Collection($collection)
	{
		$this->collections['<?php echo $entityTitleName ?>_Collection'] = $collection;
	}
<?php if ($shouldGenerateAddRemove) { ?>
	
	public function add<?php echo $entityTitleName ?>(<?php echo $className ?> $object)
	{
		//$object->setRemoteFieldId($this->getAccountId());
		//$object->setRemoteField_Object($this);
		$this->get<?php echo $entityTitleName ?>_Collection()->add($object);
		return $object;
	}
	
	public function remove<?php echo $entityTitleName ?>($objectOrId)
	{
		$removedObject = $this->get<?php echo $entityTitleName ?>_Collection()->remove($objectOrId);
		if (is_object($removedObject)) {
			// pre-coughphp 1.4: delete now
			//$removedObject->delete();
			// coughphp 1.4+: mark for delete (calling save on it or the parent object/collection will delete)
			//$removedObject->remove();
		}
		return $removedObject;
	}
<?php } ?>
