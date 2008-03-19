Application Config
==================

Two things should be done in the application's configuration file:
In your application's configuration file, just include the load file and setup database configs:

	// Configure CoughPHP
	include_once('modules/coughphp/load.inc.php');
	CoughDatabaseFactory::addConfig('main_db', array(
		'driver' => 'mysql',
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306
	));

To use a Cough class, either setup an `__autoload` method, use the `CoughLoader`, or do manual inclusion of the classes.

Setup an autoloader (recommended)
---------------------------------

We've bundled in an Autoloader class, but it has to be included explicitly.

	// Configure autoloader
	include_once('modules/coughphp/cough/Autoloader.class.php');
	Autoloader::setClassPaths(array(
		APP_PATH . 'classes/',
		SHARED_PATH . 'classes/',
		SHARED_PATH . 'models/'
	));
	Autoloader::setCacheFilePath(APP_PATH . 'tmp/class_path_cache.txt');
	Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
	function __autoload($className) {
		Autoloader::loadClass($className);
	}
