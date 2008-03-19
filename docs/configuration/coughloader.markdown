Use the CoughLoader
-------------------

Configure CoughLoader

	include_once('modules/coughphp/cough/CoughLoader.class.php');
	CoughLoader::addModelPath(APP_PATH . 'models/');
	CoughLoader::addModelPath(SHARED_PATH . 'models/');

Using it to load all classes for a given database:

	CoughLoader::loadCoughClasses('dbName');

Using it to load one class:

	CoughLoader::loadCoughClasses('dbName', 'ClassName');
 
Using it to load multiple classes:

	CoughLoader::loadCoughClasses('dbName', array('ClassName1', 'ClassName2'));

Note the class name must be the concrete object class name.  For example, if the `Author_Collection` class is needed, specify `Author`.  It will load the generated classes and then the concrete classes for both `Author` and `Author_Colleciton`.
