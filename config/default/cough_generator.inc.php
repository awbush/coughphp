<?php
/**
 * Academic Superstore Cough Generator Config
 *
 * @author Anthony Bush
 * @version $Id$
 **/


$generated = dirname(__FILE__) . '/output/';

$config = array(
	
	'phpDoc' => array(
		'author' => 'CoughGenerator',
		'package' => 'shared',
		'copyright' => 'Academic Superstore',
	),
	
	'paths' => array(
		'generated_classes' => $generated . 'generated/',
		'starter_classes' => $generated . 'concrete/',
		'file_suffix' => '.class.php',
	),
	
	'class_names' => array(
		// You can add prefixes to class names that are generated
		'prefix' => '',
		// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
		'strip_table_name_prefixes' => array('cust_', 'wfl_', 'baof_'),
		// Suffixes...
		'base_object_suffix' => '_Generated',
		'base_collection_suffix' => '_Collection_Generated',
		'starter_object_suffix' => '',
		'starter_collection_suffix' => '_Collection',
	),
	
	'field_settings' => array(
		'delete_flag_column' => 'is_retired',
		'delete_flag_value' => '1',
	),
	
);

?>