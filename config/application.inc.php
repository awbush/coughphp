<?php

// Setup some paths
define('BASE_PATH', dirname(dirname(__FILE__)) . '/');
define('CONFIG_PATH', BASE_PATH . 'config/');

// Environment
define('DEV', 1);

// Load the core generation classes
include_once(BASE_PATH . 'cough_generator/load.inc.php');

?>