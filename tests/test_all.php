<?

include_once(dirname(dirname(dirname(__FILE__))) . '/simpletest/jumpstart.inc.php');

$test = new TestSuite(dirname(__FILE__));
$test->collect(dirname(__FILE__), new AS_TestCollector());
$test->run(new HtmlReporter());

?>
