<?php

/**
 * Cough DAL (Database Adapter Layer) for the "as_database" module.
 *
 * @package as_database
 **/
class CoughAsDatabase extends As_Database implements CoughDatabaseInterface
{
	/**
	 * creates a new As_Database connection from a DSN
	 *
	 * @return CoughAsDatabase
	 * @author Lewis Zhang
	 **/
	public static function constructByConfig($dbConfig)
	{
		// driver is irrelavent, as As_Database only supports mysql
		// $driver = $dbConfig['driver'];
		
		if (isset($dbConfig['host'])) {
			$host = $dbConfig['host'];
		} else {
			$host = 'localhost';
		}
		if (isset($dbConfig['user'])) {
			$username = $dbConfig['user'];
		} else {
			$username = 'nobody';
		}
		if (isset($dbConfig['pass'])) {
			$password = $dbConfig['pass'];
		} else {
			$password = '';
		}
		if (isset($dbConfig['db_name'])) {
			$database = $dbConfig['db_name'];
		} else {
			$database = '';
		}
		if (isset($dbConfig['port'])) {
			$port = $dbConfig['port'];
		} else {
			$port = 3306;
		}
		if (isset($dbConfig['client_flags'])) {
			$clientFlags = $dbConfig['client_flags'];
		} else {
			$clientFlags = 0;
		}
		
		return new CoughAsDatabase($database, $host, $username, $password, $port, $clientFlags);
	}
	
	public function execute($sql)
	{
		// emulate execute functionality with MattDatabase
		parent::query($sql);
		return parent::getAffectedRows();
	}
	
	protected function getResultObjectFromResource($resource)
	{
		return new CoughAsDatabaseResult($resource);
	}
	
	public function getLastInsertId()
	{
		return parent::getInsertID();
	}
}
?>
