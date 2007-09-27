<?php

// Setup some paths
define('APP_PATH', dirname(dirname(__FILE__)) . '/');
define('CONFIG_PATH', APP_PATH . 'config/');
define('CLASS_PATH', APP_PATH . 'classes/');

// Environment
define('DEV', 1);

// Load the core generation classes
include(CLASS_PATH . 'load.inc.php');

?>