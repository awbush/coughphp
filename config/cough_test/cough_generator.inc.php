<?php
/**
 * Academic Superstore Cough Generator Config
 *
 * @author Anthony Bush
 * @version $Id$
 **/


$generated = dirname(dirname(dirname(__FILE__))) . '/generated/';

$config = array(
	
	'phpDoc' => array(
		'author' => 'CoughGenerator',
		'package' => 'shared',
		'copyright' => 'Academic Superstore',
	),
	
	'paths' => array(
		'generated_classes' => $generated,
		'starter_classes' => $generated,
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
		// You can use your own "AppCoughObject" class here instead, if you want.
		'extension_class_name' => 'CoughObject',
	),
	
	'field_settings' => array(
		'retired_column' => 'is_retired',
		'is_retired_value' => '1',
		'is_not_retired_value' => '0', // TODO: deprecate this. Have the code use != is_retired_value
	),
	
	// We could change any of the above on a per database setting (like the output path)...
	// 'databases' => array(
	// 	'cough_test_fk' => array(
	// 		'paths' => array(
	// 			'generated_classes' => $generated . 'cough_test_fk/',
	// 			'starter_classes' => $generated . 'cough_test_fk/',
	// 			'file_suffix' => '.class.php',
	// 		),
	// 	),
	// 	'cough_test' => array(
	// 		'paths' => array(
	// 			'generated_classes' => $generated . 'cough_test/',
	// 			'starter_classes' => $generated . 'cough_test/',
	// 			'file_suffix' => '.class.php',
	// 		),
	// 	),
	// ),
	
);

?>