<?php

/**
 * Cough Web Panel - A quick, simple, interface to the CoughGenerator
 * class.
 * 
 * @author Anthony Bush
 * @version $Id$
 * @copyright Anthony Bush (http://anthonybush.com/), 30 April, 2007
 * @package default
 **/

// Get the cough generator classes
// include('cough_generator_classes.php');

// Setup some web panel contants
define('CONFIG_FILE_SUFFIX', '.php');
define('APP_PATH', dirname(__FILE__) . '/');
define('CONFIG_PATH', APP_PATH . 'config/');
define('CLASS_PATH', APP_PATH . 'classes/');


// Catch the view data so we can wrap it in a layout.
ob_start();


// Route page 1: select a config file to use:

if (!isset($_GET['config'])) {
	
	// Load the configs
	
	$configs = array();
	$configDir = dir(CONFIG_PATH);
	while ($file = $configDir->read()) {
		if (strpos($file, '.') !== 0 && strstr($file, CONFIG_FILE_SUFFIX) == CONFIG_FILE_SUFFIX) {
			include_once(CONFIG_PATH . $file);
			if (isset($config)) {
				$configs[$file] = $config;
				unset($config);
			}
		}
	}
	
	// Display configs for selection
	
	if (count($configs) > 0) {
		echo '<p>Select a config file:</p>';
		echo '<ul>';
		foreach ($configs as $configFile => $config) {
			echo '<li><a href="?config=' . htmlentities($configFile) . '">' . htmlentities($configFile) . '</a></li>';
		}
		echo '</ul>';
	} else {
		echo '<p>No config files in "' . CONFIG_PATH . '".</p>';
	}
	
}

// Route page 2: view preview / overview from currently selected config

else {
	
	// Load the config and set some display variables
	
	$configToLoad = str_replace('../', '', $_GET['config']);
	$configFile = CONFIG_PATH . $configToLoad;
	
	$error = '';
	if (file_exists($configFile)) {
		if (is_readable($configFile)) {
			include($configFile);
			if (!isset($config)) {
				$config = array();
				// $error = 'No $config array in "' . $configFile . '".';
			}
		} else {
			$error = 'File is not readable: "' . $configFile . '".';
		}
	} else {
		$error = 'File does not exist: "' . $configFile . '".';
	}
	
	// Display preview stuff
	
	if (empty($error)) {
		?>
		
		<p>Config file loaded successfully.</p>
		
		<?php
		
		// Regenerate NOW (TODO: show preview info and require a click to regen / commit)
		include_once('/Users/awbush/Projects/Shared/modules/database_analyzer/load.inc.php');
		include_once(CLASS_PATH . 'CoughConfig.class.php');
		include_once(CLASS_PATH . 'SchemaGenerator.class.php');
		
		$configObj = new CoughConfig($config);
		
		$server = new MysqlServer($configObj->getDsn());
		$server->loadDatabase('mediapc');
		
		$schemaGenerator = new SchemaGenerator($configObj);
		$schemaGenerator->loadDatabase($server->getDatabase('mediapc'));
		$schemas  = $schemaGenerator->generateSchemas();
		
		
		
	} else {
		echo '<p>' . htmlentities($error) . '</p>';
	}
	
}


		##############
		# Controller #
		##############

		// $flashMessage = '';
		// 
		// // Action: Generate Cough Classes
		// if (isset($_GET['generate'])) {
		// 	$generator = new CoughGenerator($configArray);
		// 	if ($generator->generate()) {
		// 		$flashMessage = 'Success generating ' . $dbName . " classes.<br />\n";
		// 	} else {
		// 		$flashMessage  = 'Failure generating ' . $dbName . " classes<br />\n";
		// 		$errorMessages = $generator->getErrorMessages();
		// 		if ( ! empty($errorMessages)) {
		// 			$flashMessage .= 'Error(s) reported by CoughGenerator:' . "<br />\n";
		// 			$flashMessage .= implode('<br />', $errorMessages) . "<br />\n";
		// 		}
		// 	}
		// 	$warnings = $generator->getWarnings();
		// 	$removedFiles = $generator->getRemovedFiles();
		// 	$modifiedFiles = $generator->getModifiedFiles();
		// 	$addedFiles = $generator->getAddedFiles();
		// }



// Render layout with view contents
$layoutContent = ob_get_clean();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Cough Web Panel</title>
	<style type="text/css" media="screen">
	/* <![CDATA[ */
		
		/* Generations Table */
		.genenerations {
			border-top: 1px solid #999;
			border-left: 1px solid #999;
		}
		.genenerations th {
			background: #ccc;
			padding: 3px;
			border-right: 1px solid #999;
			border-bottom: 1px solid #999;
		}
		.genenerations td {
			padding: 3px;
			border-right: 1px solid #999;
			border-bottom: 1px solid #999;
		}
		
		/* Results */
		.results {
			border: 1px solid #999;
			margin: 1em 0;
			background: #ccc;
		}
		.result_message {
			background: #FFFF99;
			padding: 5px;
			border-bottom: 1px solid #999;
		}
		.message {
			border: 1px solid #999;
			background: #eee;
			margin: 5px;
		}
		.message .title {
			padding: 3px 5px;
		}
		.message .content {
			font-family: monospace;
			background: #fff;
			padding: 5px;
			white-space: nowrap;
			overflow: auto;
		}
	/* ]]> */
	</style>
</head>
<body>

<h1>Cough Web Panel</h1>

<div id="layout_content">
<?php echo $layoutContent; ?>
</div>

<?php if ( ! empty($flashMessage)): ?>

<div class="results">

	<div class="result_message"><?=$flashMessage?></div>

	<div class="message">
		<div class="title">Warnings</div>
		<div class="content">
		
			<?php
			if (empty($warnings)) {
				echo 'No Warnings';
			} else {
				foreach ($warnings as $warning) {
					echo $warning . "<br />\n";
				}
			}
			?>
		
		</div>
	</div>
	
	<div class="message">
		<div class="title">Added Files</div>
		<div class="content">
		
			<?php
			if (empty($addedFiles)) {
				echo 'No Added Files';
			} else {
				foreach ($addedFiles as $filename) {
					echo $filename . "<br />\n";
				}
				echo "<br />\n";
				echo 'cvs add ' . str_replace(SHARED_PATH, '', implode(' ', $addedFiles)) . '';
			}
			?>
		
		</div>
	</div>
	
	<div class="message">
		<div class="title">Removed Files</div>
		<div class="content">
		
			<?php
			if (empty($removedFiles)) {
				echo 'No Removed Files';
			} else {
				foreach ($removedFiles as $filename) {
					echo $filename . "<br />\n";
				}
				echo "<br />\n";
				echo 'cvs remove ' . str_replace(SHARED_PATH, '', implode(' ', $removedFiles)) . '';
			}
			?>
		
		</div>
	</div>
	
	<div class="message">
		<div class="title">Modified Files</div>
		<div class="content">
		
			<?php
			if (empty($modifiedFiles)) {
				echo 'No Modified Files';
			} else {
				foreach ($modifiedFiles as $filename) {
					echo $filename . "<br />\n";
				}
			}
			?>
		
		</div>
	</div>
	
</div>

<?php endif; ?>


</body>
</html>
