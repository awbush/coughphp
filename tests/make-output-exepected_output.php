#!/usr/bin/env php
<?php
foreach (glob(dirname(__FILE__) . '/cough_generator/test_configs/test_config*/output/*/*') as $srcFile)
{
	$destFile = str_replace('/output/', '/expected_output/', $srcFile);
	passthru('cat ' . escapeshellarg($srcFile) . ' > ' . escapeshellarg($destFile));
}
