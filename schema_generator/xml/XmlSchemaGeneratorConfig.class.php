<?php

/**
 * XML Schema Generator takes an XML schema file and generates the Schema
 * objects that CoughGenerator takes.
 *
 * @package schema_generator
 * @author Anthony Bush
 **/
class XmlSchemaGeneratorConfig extends CoughConfig {
	
	public static function constructFromFile($filePath) {
		include($filePath);
		return new XmlSchemaGeneratorConfig($config);
	}
	
	protected function initConfig() {
		$this->config = array();
	}
	
}

?>