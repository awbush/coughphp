<?php

require_once(dirname(__FILE__) . '/TestCoughAsDatabase.class.php');

// This test won't pass until CoughPdoDatabase* classes in the "dal/pdo" folder are filled in.
class TestCoughPdoDatabase extends TestCoughAsDatabase
{
	protected $adapterName = 'pdo';
	protected $resultObjectClassName = 'CoughPdoDatabaseResult';
	
	public function loadAdapterModule()
	{
		// anything to do here for PDO?
	}
}

?>