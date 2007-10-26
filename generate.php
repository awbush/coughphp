<?php

// Include the Cough generation classes
include_once(dirname(__FILE__) . '/cough_generator/load.inc.php');

$facade = new CoughGeneratorFacade();
$facade->generate(dirname(__FILE__) . '/cough/tests/config/');

?>