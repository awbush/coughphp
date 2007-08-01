<?php

class CoughGenerator {
	
	protected $generatedClasses = array();
	
	public function generatePhpCoughClasses($schema) {
		
		// Generate the class
		$className = 'Test';
		$contents = '<?php class ' . $className . ' extends CoughObject {} ?>';
		$coughClass = new CoughClass($className, $contents);
		
		// Add the class
		$this->addGeneratedClass($coughClass);
		
		// Return the generated classes.
		return $this->generatedClasses;
	}
	
	public function addGeneratedClass($coughClass) {
		$this->generatedClasses[] = $coughClass;
	}
}

?>