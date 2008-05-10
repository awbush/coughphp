<?php

require_once('AS_TestCollector.class.php');

define('APP_PATH', dirname(dirname(__FILE__)) . '/');

// Include the simpletest library
error_reporting(E_ALL & ~E_STRICT); // simpletest is not PHP5 strict :(
define('SIMPLETEST_PATH', dirname(__FILE__) . '/simpletest_1.0.1beta2/');
require_once(SIMPLETEST_PATH . 'unit_tester.php');
require_once(SIMPLETEST_PATH . 'web_tester.php');
require_once(SIMPLETEST_PATH . 'reporter.php');
