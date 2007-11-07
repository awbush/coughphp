<?php

/**
 * The CoughGeneratorFacade class provides a simple interface to the process of
 * generating a schema from a database, manipulating it and converting FKs to
 * relationships, and passing that info to the CoughGenerator which generates the
 * classes.
 * 
 * CoughGeneratorFacade will provide at least an easy way to:
 * 
 *     1) Given a config name, generate the files.
 *     2) Given a config name, provide information about what would be generated
 *        (e.g. added, removed, and modified files).
 * 
 * Facade design pattern: "create a simplified interface of an existing interface to ease usage for common tasks"
 *
 * @package cough_generator
 * @author Anthony Bush
 **/
class CoughGeneratorFacade {
	
	/**
	 * Whether or not to echo what's happening to the screen.
	 *
	 * @var boolean
	 **/
	protected $verbose = false;
	
	/**
	 * Enable verbose mode
	 *
	 * @return void
	 * @see $verbose
	 **/
	public function enableVerbose() {
		$this->verbose = true;
	}
	
	/**
	 * Disable verbose mode
	 *
	 * @return void
	 * @see $verbose
	 **/
	public function disableVerbose() {
		$this->verbose = false;
	}
	
	public function generate($configNameOrPath) {
		$this->generateFromConfigPath($this->getConfigPath($configNameOrPath));
	}
	
	public function showStatus($configNameOrPath) {
		$this->showStatusFromConfigPath($this->getConfigPath($configNameOrPath));
	}
	
	protected function getConfigPath($configNameOrPath) {
		if (file_exists($configNameOrPath)) {
			return rtrim($configNameOrPath, '/') . '/';
		} else {
			return dirname(dirname(__FILE__)) . '/config/' . $configNameOrPath . '/';
		}
	}
	
	protected function generateFromConfigPath($configPath) {
		
		try {
			if (!file_exists($configPath)) {
				echo 'Config path does not exist: ' . $configPath . "\n";
				return;
			}
			if (!is_dir($configPath)) {
				echo 'Config path is not a directory: ' . $configPath . "\n";
				return;
			}
			
			// Which config to use?
			$schemaGeneratorConfigFile = $configPath . 'database_schema_generator.inc.php';
			$coughGeneratorConfigFile  = $configPath . 'cough_generator.inc.php';

			// Get the database config
			$schemaGeneratorConfig = DatabaseSchemaGeneratorConfig::constructFromFile($schemaGeneratorConfigFile);

			// Load the schema into memory
			$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
			if ($this->verbose) {
				$schemaGenerator->enableVerbose();
			}
			$schema = $schemaGenerator->generateSchema();

			// Dump some verbose messages.
			// echo "\n";
			// $schema->outputRelationshipCounts();

			// echo "\n" . 'About to run the SchemaManipulator and re-output the relationships.' . "\n";

			// Manipulate the schema (to add any missed relationships, e.g.)
			$manipulator = new SchemaManipulator($schemaGeneratorConfig);
			if ($this->verbose) {
				$manipulator->enableVerbose();
			}
			$manipulator->manipulateSchema($schema);

			// Dump some verbose messages again to see if the manipulator added anything.
			// echo "\n";
			// $schema->outputRelationshipCounts();

			// Get the cough generator config
			$coughGeneratorConfig = CoughGeneratorConfig::constructFromFile($coughGeneratorConfigFile);

			// Generate files into memory
			$coughGenerator = new CoughGenerator($coughGeneratorConfig);
			$classes = $coughGenerator->generateCoughClasses($schema);
			
			// Add some spacing if verbose mode is on
			if ($this->verbose) {
				echo "\n";
			}
			
			// Write files to disk
			$coughWriter = new CoughWriter($coughGeneratorConfig);
			if (!$coughWriter->writeClasses($classes)) {
				echo 'Trouble writing classes:' . "\n";
				echo '------------------------' . "\n";
				foreach ($coughWriter->getErrorMessages() as $message) {
					echo $message . "\n";
				}
				echo "\n";
			} else {
				$lineCount = 0;
				foreach ($classes as $class) {
					$lineCount += substr_count($class->getContents(),"\n");
				}
				echo 'Success writing ' . count($classes) . ' classes (' . number_format($lineCount) . ' lines) with ' . $schema->getNumberOfHasOneRelationships() . ' one-to-one relationships and ' . $schema->getNumberOfHasManyRelationships() . ' one-to-many relationships!' . "\n";
			}
			
			if ($this->verbose) {
				echo "\n" . 'PHP memory usage:' . "\n";
				echo number_format(memory_get_usage()) . " used\n";
				if (version_compare(phpversion(), '5.2.0', '>=')) {
					echo number_format(memory_get_usage(true)) . " allocated\n";
				}
			}

		} catch (Exception $e) {
			echo $e->getMessage() . "\n";
		}
	}
	
	protected function showStatusFromConfigPath($configPath) {
		
		try {
			if (!file_exists($configPath)) {
				echo 'Config path does not exist: ' . $configPath . "\n";
				return;
			}
			if (!is_dir($configPath)) {
				echo 'Config path is not a directory: ' . $configPath . "\n";
				return;
			}
			
			// Which config to use?
			$schemaGeneratorConfigFile = $configPath . 'database_schema_generator.inc.php';
			$coughGeneratorConfigFile  = $configPath . 'cough_generator.inc.php';
			
			// Get the database config
			$schemaGeneratorConfig = DatabaseSchemaGeneratorConfig::constructFromFile($schemaGeneratorConfigFile);
			
			// Load the schema into memory
			$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
			if ($this->verbose) {
				$schemaGenerator->enableVerbose();
			}
			$schema = $schemaGenerator->generateSchema();
			
			// Dump some verbose messages.
			// echo "\n";
			// $schema->outputRelationshipCounts();
			
			// echo "\n" . 'About to run the SchemaManipulator and re-output the relationships.' . "\n";
			
			// Manipulate the schema (to add any missed relationships, e.g.)
			$manipulator = new SchemaManipulator($schemaGeneratorConfig);
			if ($this->verbose) {
				$manipulator->enableVerbose();
			}
			$manipulator->manipulateSchema($schema);
			
			// Dump some verbose messages again to see if the manipulator added anything.
			// echo "\n";
			// $schema->outputRelationshipCounts();
			
			// Get the cough generator config
			$coughGeneratorConfig = CoughGeneratorConfig::constructFromFile($coughGeneratorConfigFile);
			
			// Generate files into memory
			$coughGenerator = new CoughGenerator($coughGeneratorConfig);
			$classes = $coughGenerator->generateCoughClasses($schema);
			
			// Add some spacing if verbose mode is on
			if ($this->verbose) {
				echo "\n";
			}
			
			// Show info...
			
			// Keep track of all the output file paths (we'll scan them for files that aren't being used)
			$filePaths = array();
			
			// Keep a map of full file name -> CoughClass object
			$generatedClasses = array();
			
			// Keep track of added, removed, and modified file information
			$addedFiles = array();
			$starterModifiedFiles = array();
			$generatedModifiedFiles = array();
			$removedFiles = array();
			$numFilesWithNoChange = 0;
			
			foreach ($classes as $class) {
				$filePaths[$coughGeneratorConfig->getClassFilePath($class)] = true;
				$fileName = $coughGeneratorConfig->getClassFileName($class);
				$generatedClasses[$fileName] = $class;
				
				// Go ahead and check if the file was added or modified
				if (!file_exists($fileName)) {
					$addedFiles[] = $fileName;
				} else {
					$currentFileContents = file_get_contents($fileName);
					if ($currentFileContents == $class->getContents()) {
						$numFilesWithNoChange++;
					} else {
						if ($class->isStarterClass()) {
							$starterModifiedFiles[] = $fileName;
						} else {
							$generatedModifiedFiles[] = $fileName;
						}
					}
				}
			}
			
			// Check for removed files:
			foreach ($filePaths as $dir => $shouldScan) {
				if (file_exists($dir) && is_readable($dir)) {
					$files = scandir($dir);
					foreach ($files as $file) {
						if (preg_match('|^[^.].*\.class\.php$|', $file)) {
							if (!isset($generatedClasses[$dir . $file])) {
								$removedFiles[] = $dir . $file;
							}
						}
					}
				}
			}
			
			// Output Removed Files
			echo "\n";
			echo count($removedFiles) . ' Removed Files' . "\n";
			foreach ($removedFiles as $file) {
				echo $file . "\n";
			}
			
			// Output Added Files
			echo "\n";
			echo count($addedFiles) . ' Added Files' . "\n";
			foreach ($addedFiles as $file) {
				echo $file . "\n";
			}
			
			// Output Modified Files
			echo "\n";
			echo count($starterModifiedFiles) . ' Modified Files (which will not be modified since they are starter classes)' . "\n";
			foreach ($starterModifiedFiles as $file) {
				echo $file . "\n";
			}
			echo "\n";
			echo count($generatedModifiedFiles) . ' Modified Files (which will be overrwitten)' . "\n";
			foreach ($generatedModifiedFiles as $file) {
				echo $file . "\n";
			}
			
			echo "\n";
			echo $numFilesWithNoChange . ' files with no change.' . "\n";
			
			// Output stats
			$lineCount = 0;
			foreach ($classes as $class) {
				$lineCount += substr_count($class->getContents(),"\n");
			}
			
			echo "\n";
			echo 'Statistics of what will be generated:' . "\n";
			echo '-------------------------------------' . "\n";
			echo 'Number of classes: ' . count($classes) . "\n";
			echo 'Number of lines: ' . number_format($lineCount) . "\n";
			echo 'One-to-one relationships: ' . $schema->getNumberOfHasOneRelationships() . "\n";
			echo 'One-to-many relationships: ' . $schema->getNumberOfHasManyRelationships() . "\n";
			
			if ($this->verbose) {
				echo "\n" . 'PHP memory usage:' . "\n";
				echo number_format(memory_get_usage()) . " used\n";
				if (version_compare(phpversion(), '5.2.0', '>=')) {
					echo number_format(memory_get_usage(true)) . " allocated\n";
				}
			}

		} catch (Exception $e) {
			echo $e->getMessage() . "\n";
		}
	}
	
}

?>