<?

class AS_TestCollector {

	protected $test = null;
	
	public function collect(&$test, $path) {
		$this->test = $test;
		$this->addTestsInDir($path);	
	}
	
	public function addTestsInDir($path) {
		//echo('addTestsInDir("' . $path . '")' . "\n");
		
		$path = rtrim($path, '/');
		if (preg_match('|/tests$|i', $path)) {
			$includeFilesInThisDir = true;
		} else {
			$includeFilesInThisDir = false;
		}

		// Scan for directories named "tests" and add all the tests in those directories.
		$testDir = dir($path);
		while ($f = $testDir->read()) {
			// Exclude "hidden" directories
			if (strpos($f, '.') !== 0) {
				// Recursively scan non-version control directories
				if (is_dir($path . '/' . $f) && strtolower($f) != 'cvs') {
					$this->addTestsInDir($path . '/' . $f);
				}
				// Include .class.php files directly inside of a "tests" directory
				else if ($includeFilesInThisDir && strstr($f, '.class.php') == '.class.php') {
					$this->test->addTestFile($path . '/' . $f);
				}
			}
		}
	}
}

?>
