<?

/**
 * Copy this file to any directory to have all the test files in that directory
 * and all sub directories run.
 * 
 * The criteria for a "test file" is that its full path match:
 *     "|/tests/[^/]+.class.php$|i"
 * 
 * Only classes extending a simpletest class will be run.
 **/

include_once('load.inc.php');

$testDir = dirname(dirname(__FILE__));
$test = new TestSuite('Testing all shared modules');
$test->collect($testDir, new AS_TestCollector());
$test->run(new HtmlReporter());

?>
