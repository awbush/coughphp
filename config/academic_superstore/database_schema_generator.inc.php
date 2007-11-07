<?php
/**
 * Default DatabaseSchemaGenerator configuration options.
 * 
 * You don't have to pass these to the schema generator as it will use
 * reasonable defaults. The are replicated here to make them easy to change.
 *
 * @package CoughPHP
 * @author Anthony Bush
 **/


$config = array(
	// REQUIRED CONFIG
	
	// All databases will be scanned unless specified in the 'databases' parameter in the OPTIONAL CONFIG SECTION.
	'dsn' => array(
		'host' => 'dev',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'driver' => 'mysql'
	),
	
	// OPTIONAL ADDITIONAL CONFIG
	
	'database_settings' => array(
		'include_databases_matching_regex' => '/^(content|customer|inventory|superstore|user|vendor_import)$/',
	),
	
	'table_settings' => array(
		// This match setting is so the database scanner can resolve relationships better, e.g. know that when it sees "ticket_id" that a "wfl_ticket" table is an acceptable match.
		'match_table_name_prefixes' => array('cust_','wfl_','baof_','inv_'), // Example: array('cust_', 'wfl_', 'baof_'),
		// You can ignore tables all together, too:
		'exclude_tables_matching_regex' => '/(^(bak_|temp_))|((_bak)$)/', // ignore tables that start with bak_ or temp_ or end with _bak
	),
	
	'field_settings' => array(
		// In case of non FK detection, you can have the Database Schema Generator check for ID columns matching this regex.
		// This is useful, for example, when no FK relationships set up). The first parenthesis match will be used to search
		// for tables
		'id_to_table_regex' => array('/^parent_(.*)_id$/', '/^billing_(.*)_id$/', '/^shipping_(.*)_id$/', '/^(.*)_id/'),
	),
	
	'databases' => array(
		'content' => array(),
		'customer' => array(),
		'inventory' => array(),
		'superstore' => array(),
		'user' => array(
			
			// Override some ignore table settings for the user database.
			'table_settings' => array(
				'exclude_tables_matching_regex' => '/(^(bak_|temp_))|((_bak)$|^(address)$)/',
			),
			
			
			// 'tables' => array(
			// 	'baof_login2role' => array(
			// 		// Support for this does not exist yet. NOTE a potential problem here is that by specifying this explicit table the others are skipped. We should fix that before adding this feature (i.e. the ability to override table level settings *without* making it so other tables are then skipped)
			// 		// // Add additional foreign keys in addition to the ones that are already detected by FKs and naming conventions.
			// 		// 'additional_foreign_keys' => array(
			// 		// 	array(
			// 		// 		'foreign_table_name' => 'baof_workflow_user',
			// 		// 		'reference' => array(
			// 		// 			'login_id' => 'workflow_user_id',
			// 		// 			'some_other_id' => 'sdlfkjsadflkj',
			// 		// 		)
			// 		// 	),
			// 		// 	// Multi-key relationships can be added by adding another entry to the 'key' array:
			// 		// 	// array(
			// 		// 	// 	'foreign_table_name' => 'baof_workflow_user',
			// 		// 	// 	'reference' => array(
			// 		// 	// 		'local_field1_id' => 'foreign_field1_id',  // in XML it might be: <reference local="local_field1_id" foreign="ref_field1_id" />
			// 		// 	// 		'local_field2_id' => 'foreign_field2_id',
			// 		// 	// 	)
			// 		// 	// ),
			// 		// )
			// 	)
			// )
		),
		'vendor_import' => array(),
	),
	
);

?>
