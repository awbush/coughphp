<?php

$generated = dirname(__FILE__) . '/output/';

$config = array(
	
	'phpDoc' => array(
		'author' => 'CoughGenerator',
		'package' => 'default',
	),
	
	'paths' => array(
		'generated_classes' => $generated . 'generated/',
		'starter_classes' => $generated . 'concrete/',
		'file_suffix' => '.class.php',
	),
	
	'class_names' => array(
		'prefix' => '',
		'strip_table_name_prefixes' => array(),
		'base_object_suffix' => '_Generated',
		'base_collection_suffix' => '_Collection_Generated',
		'starter_object_suffix' => '',
		'starter_collection_suffix' => '_Collection',
		'object_extension_class_name' => 'CoughObject',
	),
	
);

?>