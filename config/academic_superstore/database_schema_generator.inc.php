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
	
	'table_settings' => array(
		// This match setting is so the database scanner can resolve relationships better, e.g. know that when it sees "ticket_id" that a "wfl_ticket" table is an acceptable match.
		'match_table_name_prefixes' => array('cust_','wfl_','baof_','inv_'), // Example: array('cust_', 'wfl_', 'baof_'),
		// You can ignore tables all together, too:
		'ignore_tables_matching_regex' => '/(^(bak_|temp_))|((_bak)$)/', // ignore tables that start with bak_ or temp_ or end with _bak
	),
	
	'field_settings' => array(
		// In case of non FK detection, you can have the Database Schema Generator check for ID columns matching this regex.
		// This is useful, for example, when no FK relationships set up). The first parenthesis match will be used to search
		// for tables
		'id_to_table_regex' => array('/^(.*)_id/', '/^parent_(.*)_id$/', '/^billing_(.*)_id$/', '/^shipping_(.*)_id$/'),
	),
	
	'databases' => array(
		'content' => array(),
		'customer' => array(),
		'inventory' => array(),
		'superstore' => array(),
		'user' => array(
			// I DON'T LIKE THIS IDEA:
			// 'skip_unspecified_tables' => true, // This may be the solution to the problem mentioned below.

			// I LIKE THIS IDEA: (require the user to explicitly name tables in the database they don't want included). Note that this is in addition to the 'table_settings/ignore_tables_matching_regex' option, and that it should be added inside the table_settings array instead of floating out here.
			// 'ignore_tables' => array(), // array of table names to ingore during scanning. This may be the solution to the problem mentioned below.
			// ACTUALLY, you don't need that option because 'table_settings/ignore_tables_matching_regex' allows for this functionality, e.g. I can ignore tables starting with bak_ or temp_, ending in _bak, or exactly "some_table_i_hate" or "some_other_table_thats_useless" using:
			// 'ignore_tables_matching_regex' => '/(^(bak_|temp_))|((_bak)$)|(^(some_table_i_hate|some_other_table_that_is_useless)$)/'
			// while it might not be trivial to all people, I would expect the intended audience (PHP Developer) to consider it trivial.
				
			
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