<?php
/**
 * Lightweight config example that only customizes the output path
 **/

$generated = dirname(__FILE__) . '/output/';

$config = array(
	'paths' => array(
		'generated_classes' => $generated . 'generated/',
		'starter_classes' => $generated . 'concrete/',
		'file_suffix' => '.class.php',
	),
);

?>