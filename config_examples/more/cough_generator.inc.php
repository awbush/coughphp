<?php

$generated = dirname(__FILE__) . '/output/';

$config = array(
	
	'phpDoc' => array(
		'author' => 'CoughGenerator',
		'package' => 'default',
		'copyright' => '',
	),
	
	'paths' => array(
		'generated_classes' => $generated . 'generated/',
		'starter_classes' => $generated . 'concrete/',
		'file_suffix' => '.class.php',
	),
	
	'load_sql_inner_joins' => 'disabled', // valid options: enabled, disabled
	'generate_has_one_methods' => 'all', // valid options: all, none, or array of databases to generate join methods for.
	'generate_has_many_methods' => 'all', // valid options: all, none, or array of databases to generate join methods for.
	
	'class_names' => array(
		// You can add prefixes to class names that are generated
		'prefix' => '',
		// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
		'strip_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
		// Suffixes...
		'base_object_suffix' => '_Generated',
		'base_collection_suffix' => '_Collection_Generated',
		'starter_object_suffix' => '',
		'starter_collection_suffix' => '_Collection',
		// You can use your on "AppCoughObject" class here instead, if you want.
		'object_extension_class_name' => 'CoughObject',
		'collection_extension_class_name' => 'CoughCollection',
	),
	
	'field_settings' => array(
		'id_regex' => '/^(.*)_id$/',
	),
	
	'databases' => array(
		// An example of how to override some of the config for just a specific database
		'some_database' => array(
			'class_names' => array(
				'prefix' => 'wee_',
			),
			'paths' => array(
				'generated_classes' => $generated . 'some_database/generated/',
				'starter_classes' => $generated . 'some_database/concrete/',
			),
		),
	)
);

?>