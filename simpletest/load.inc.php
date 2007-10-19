<?php

require_once('AS_TestCollector.class.php');

define('APP_PATH', dirname(dirname(__FILE__)) . '/');

// Include the simpletest library
define('SIMPLETEST_PATH', dirname(__FILE__) . '/simpletest_1.0.1beta2/');
require_once(SIMPLETEST_PATH . 'unit_tester.php');
require_once(SIMPLETEST_PATH . 'web_tester.php');
require_once(SIMPLETEST_PATH . 'reporter.php');

?>
