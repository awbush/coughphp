<?php

include_once('AS_TestCollector.class.php');

define('APP_PATH', dirname(dirname(__FILE__)) . '/');

// Include the simpletest library
define('SIMPLETEST_PATH', dirname(__FILE__) . '/simpletest_1.0.1beta2/');
include_once(SIMPLETEST_PATH . 'unit_tester.php');
include_once(SIMPLETEST_PATH . 'web_tester.php');
include_once(SIMPLETEST_PATH . 'reporter.php');

?>
