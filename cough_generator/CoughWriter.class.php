<?php

/**
 * Takes CoughClass objects and writes them disk.
 * 
 * It can check to see what is already on the disk, possibly provide access
 * to the differences, or merge the differences, but the main focus is just on
 * actually writing the contents.
 * 
 * @package cough_generator
 * @author Anthony Bush
 **/
class CoughWriter {
	
	/**
	 * Error message storage
	 *
	 * @var array
	 **/
	protected $errorMessages = array();
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	/**
	 * Construct with optional configuration parameters.
	 * 
	 * @param mixed $config - either an array of configuration variables or a pre-constructed CoughGeneratorConfig object.
	 * @return void
	 **/
	public function __construct($config = array()) {
		$this->initConfig($config);
	}
	
	/**
	 * Initialize the configuration object given an array or pre-constructed
	 * configuration object.
	 * 
	 * @param mixed $config - an array or CoughGeneratorConfig object (yes, the CoughWriter shares the CoughGenerator's config object.)
	 * @return void
	 * @throws Exception
	 **/
	public function initConfig($config) {
		if ($config instanceof CoughGeneratorConfig) {
			$this->config = $config;
		} else if (is_array($config)) {
			$this->config = new CoughGeneratorConfig($config);
		} else {
			throw new Exception('First parameter must be an array or CoughGeneratorConfig object.');
		}
	}
	
	/**
	 * Retrieve any error messages set by other methods.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function getErrorMessages() {
		return $this->errorMessages;
	}
	
	/**
	 * Writes all the classes to disk, skipping starter classes if they already exist.
	 *
	 * @return boolean - true if no errors, false if not (use {@link getErrorMessages()})
	 * @author Anthony Bush
	 **/
	public function writeClasses($classes) {
		$this->errorMessages = array();
		
		// Write all classes to disk, but only write non-starter classes or the starter classes that haven't been written already.
		foreach ($classes as $class) {
			$fileName = $this->config->getClassFileName($class);
			if (!$class->isStarterClass() || !file_exists($fileName)) {
				$this->writeToFile($fileName, $class->getContents());
			}
		}
		
		// Return error status
		if (!empty($this->errorMessages)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Puts the given contents into the given file name.
	 *
	 * @param string $fileName the file name to write to.
	 * @param string $fileContents the contents to write.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function writeToFile($fileName, $fileContents) {
		if (file_exists($fileName)) {
			if (is_writable($fileName)) {
				$bytesWritten = file_put_contents($fileName, $fileContents);
				if ($bytesWritten == 0) {
					$this->errorMessages[] = 'Wrote zero bytes to ' . $fileName;
				}
			} else {
				$this->errorMessages[] = 'Unable to write to file (no permissions on file): ' . $fileName;
			}
		} else {
			$dirName = dirname($fileName);
			
			if (!file_exists($dirName)) {
				mkdir($dirName, 0777, true);
			} else if (!is_dir($dirName)) {
				$this->errorMessages[] = 'An existing file conflicts with the generated directory path: ' . $fileName;
				return;
			}
			
			if (is_writeable($dirName)) {
				$bytesWritten = file_put_contents($fileName, $fileContents);
				if ($bytesWritten == 0) {
					$this->errorMessages[] = 'Wrote zero bytes to ' . $fileName;
				}
			} else {
				$this->errorMessages[] = 'Unable to write to file (no permissions on directory): ' . $fileName;
			}
		}
	}
	
}

?>
