<?php

class CoughGeneratorConfig extends CoughConfig {
	
	protected function initConfig() {
		$generated = dirname(dirname(__FILE__)) . '/generated/';

		$this->config = array(
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
				// You can add prefixes to class names that are generated
				'prefix' => '',
				// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
				'strip_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
				// Suffixes...
				'base_object_suffix' => '_Generated',
				'base_collection_suffix' => '_Collection_Generated',
				'starter_object_suffix' => '',
				'starter_collection_suffix' => '_Collection',
			),
			'field_settings' => array(
				'retired_column' => 'is_retired',
				'is_retired_value' => '1',
				'is_not_retired_value' => '0', // TODO: deprecate this. Have the code use != is_retired_value
			),

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
		);
	}
	
}

?>