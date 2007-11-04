<?php

/**
 * Copy this file to any directory to have all the test files in that directory
 * and all sub directories run.
 * 
 * The criteria for a "test file" is that its full path match:
 *     "|/tests/[^/]+.class.php$|i"
 * 
 * Only classes extending a simpletest class will be run.
 **/

require_once(dirname(__FILE__) . '/load.inc.php');

$testDir = dirname(dirname(__FILE__));
$test = new TestSuite('Testing all shared modules');
$test->collect($testDir, new AS_TestCollector());
if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

?>
