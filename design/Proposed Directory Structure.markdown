Proposed Cough Directory Structure Layout
=========================================

coughphp.tar.gz

	coughphp/
		cough/
			CoughObject.class.php
			CoughCollection.class.php
			CoughInstancePool.class.php
			CoughLoader.class.php
			CoughIterator.class.php
		cough_generator/
			CoughClass.class.php
			CoughConfig.class.php
			CoughGenerator.class.php
			CoughGeneratorConfig.class.php
			CoughWriter.class.php
		schema/
			Schema.class.php
			SchemaColumn.class.php
			SchemaDatabase.class.php
			SchemaRelationship.class.php
			SchemaTable.class.php
		schema_generator/
			SchemaGenerator.class.php
			database/
				DatabaseSchemaGenerator.class.php
				DatabaseSchemaGeneratorConfig.class.php
				drivers/
					base/ -- TODO Should we switch these names to be *Driver instead of Driver*?
						DriverDatabase.class.php
						DriverServer.class.php
						DriverTable.class.php
					mysql/
						MysqlDatabase.class.php
						MysqlServer.class.php
						MysqlTable.class.php
			xml/
				XmlSchemaGenerator.class.php
				XmlSchemaGeneratorConfig.class.php
		dal/ -- TODO rename this? it's "database abstraction layers"
			matt_database/ -- TODO: Rename and possibly move this
		dalal/ -- TODO: rename this, it's the "database abstraction layer abstraction layer"
			Lz_DatabaseFactory
			drivers/
				base/
					Lz_Database
					Lz_DatabaseResult
				matt_database/
					Lz_MattDatabase
					Lz_MattDatabaseResult
				pdo/
					Lz_PdoDatabase
					Lz_PdoDatabaseResult
		generate.php
			[command line tool for generating code]
		web_panel.php
			[web-based tool for generating code]
		
		... configuration stuff that we should consider moving outside of the coughphp directory??? ...
		
		config/
			[each config set gets its own directory]
			cough_test/
				cough_generator.inc.php
				database_schema_generator.inc.php
			default/
				cough_generator.inc.php
				database_schema_generator.inc.php
		
		generated/
			[the default config we specify could output generated files here]
		
		... auxilary files not required to run Cough ...
		
		tests/
			[unit tests]
		sample-app/
			[sample SQL for creating different schema, both with and without Foreign Keys, and the configs used for their generation]
		docs/
			[documentation specific to this checkout of code w/ link to online docs including API]
