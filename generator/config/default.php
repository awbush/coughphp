<?php
/**
 * Academic Superstore Cough Generator Config
 *
 * @author Anthony Bush
 * @version $Id$
 **/


/**
 * CONFIG LEVEL 1
 * 
 * mini DSN (no database name)
 * generation paths
 * class names
 *
 * almost exactly the same as previous class-based config, except
 **/

$generated = dirname(__FILE__) . '/generated/';

$config = array(
	'phpDoc' => array(
		'author' => 'CoughGenerator',
		'package' => 'shared',
		'copyright' => 'Academic Superstore',
	),
	'paths' => array(
		'generated_classes' => $generated . 'generated_classes/',
		'starter_classes' => $generated . 'starter_classes/',
		'file_suffix' => '.class.php',
	),
	'class_names' => array(
		'prefix' => '',
		'base_object_suffix' => '_Generated',
		'base_collection_suffix' => '_Collection_Generated',
		'starter_object_suffix' => '',
		'starter_collection_suffix' => '_Collection',
	),
	'table_settings' => array(
		// This match setting is so the database scanner can resolve relationships better, e.g. know that when it sees "ticket_id" that a "wfl_ticket" table is an acceptable match.
		'match_table_name_prefixes' => array('cust_', 'wfl_', 'baof_'),
		// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
		'strip_table_name_prefixes' => array('cust_', 'wfl_', 'baof_'),
		// You can ignore tables all together, too:
		'ignore_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
	),
	'field_settings' => array(
		'id_suffix' => '_id',
		'retired_column' => 'is_retired',
		'is_retired_value' => '1',
		'is_not_retired_value' => '0', // TODO: deprecate this. Have the code use != is_retired_value
	),
	
	// All databases will be scanned unless specified in the 'databases' parameter.
	'dsn' => array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		
		// Now, we can override the global config on a per database level.
		// 'databases' => array(
		// 	'user' => array(
		// 		'class_names' => array(
		// 			'prefix' => 'usr_'
		// 		),
		// 		'table_settings' => array(
		// 			'strip_table_name_prefixes' => array('wfl_', 'baof_'),
		// 		),
		// 
		// 		// Furthermore, we can override the table level settings
		// 		'tables' => array(
		// 			'table_name' => array(
		// 				'field_settings' => array(
		// 					'id_suffix' => '_id',
		// 					'retired_column' => 'status',
		// 					'is_retired_value' => 'cancelled',
		// 				),
		// 			),
		// 		),
		// 	),
		// ),
	),
);

?>
