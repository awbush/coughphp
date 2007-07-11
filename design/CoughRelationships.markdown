<?php

class CoughRelationships {
	
	// Single repository for all table definitions:
	protected $tableDefinitions = array(
		'db_name' => array(
			'db_factory_alias' => 'crump', // if a db_factory_alias is defined, the object will use it instead of the dbName which is the default. FINALLY a way to customize that (helps with transactions because you can force all objects that might use different databases to share the same database object)
			'tables' => array(
				'table_name' => array(
					'element_class' => 'usr_WflTicket',
					'collection_class' => 'usr_WflTicket_Collection',
					'columns' => array(
						'db_column_name' => array(
							'default' => null,
							'is_null_allowed' => true,

						)
					),
					'has_one' => array(
						'objectAliasName' => array(
							'src' => 'account_id',
							'dest' => array(
								'db_name' => 'customer',
								'table_name' => 'cust_account',
								'column_name' => 'account_id'
							)
						),
						// so FK => one-to-one object? YES for the table it is on. In this case a Ticket can check cust_account because account_id is available.
					)
					'has_many' => array(
						'collectionAliasName' => array(
							'src' => 'ticket_id',
							'dest' => array(
								'db_name' => 'user', // couldn't we do db_factory_alias? Don't have to becase the defintions links db_name to db_factory_alias. BTW, this allows cross server joins (sort of), e.g. pull an order and it's lines from two different servers, but it's not really a join.
								'table_name' => 'wfl_ticket_line',
								'column_name' => 'ticket_id'
							)
						),
					)
					'habtm' => array(
						'collectionAliasName' => array(
							'src' => 'product_id',
							'join' => array(
								'db_name' => 'content',
								'table_name' => 'product2os',
								'src_column_name' => 'product_id',
								'dest_column_name' => 'os_id',
							),
							'dest' => array(
								'db_name' => 'content',
								'table_name' => 'os',
								'column_name' => 'os_id'
							)
						),
					)
					// These arrays allow FULL customization of all databaes, table, and column names and thus supports the ability to add ANY joins that Cough might have missed. And, if you use the XML schema method you don't have to teach Cough how to scan your database, you can simply tell it at a pre-generation level.
				)
			)
		)
	);
}

// Option 1: Generator everything here.

// Option 2: Configure at runtime with only the info as it is needed, e.g. product wants to check os object then we have to first load the os class files (which will have code that addes to the relationships).






?>