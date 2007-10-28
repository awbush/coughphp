<?php

/**
 * AS Database abstraction layer wrapper
 *
 * @package default
 * @author Lewis Zhang
 **/
class CoughAsDatabaseAdapter extends CoughAbstractDatabaseAdapter
{	
	/**
	 * creates a new As_Database connection from a DSN
	 * NOTE: this may or may not apply after the DatabaseFactory changes to include CoughAbstractDatabaseAdapter
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDbConfig($dbConfig)
	{
		// Load the AS DAL framework.
		require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/dal/as/load.inc.php');
		
		// driver is irrelavent, as As_Database only supports mysql
		$driver = $dbConfig['driver'];
		
		$host = $dbConfig['host'];
		$username = $dbConfig['user'];
		$password = $dbConfig['pass'];
		$database = $dbConfig['db_name'];
		
		return new CoughAsDatabaseAdapter(new As_Database($database, $host, $username, $password));
	}
	
	public function query($sql)
	{
		$result = $this->db->query($sql);
		return CoughAsDatabaseResultAdapter::retrieveByResult($result);
		// TODO: As_Database always returns an object, so we need to find out how to return false when the query failed
		// 2007-10-28/AWB: It throws an error when the query fails.  If that is not acceptable we can catch the error here and return false in that case.  I think throwing an error is the more correct thing to do though as it provides access to error messages, etc.  If that sounds good, we should make other database adapters throw errors here instead of returning false.
	}
	
	public function execute($sql)
	{
		// emulate execute functionality with MattDatabase
		$this->db->query($sql);
		return $this->db->getAffectedRows();
	}
	
	public function getLastInsertId()
	{
		return $this->db->getInsertID();
	}
	
	public function dbQuote($string)
	{
		return $this->db->quote($string);
	}
	
	/**
	 * returns the escaped version of the provided string
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function escape($string)
	{
		return $this->db->escape($string);
	}
	
	/**
	 * selects the specified database for this connection
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function selectDb($databaseName)
	{
		$this->db->selectDB($databaseName);
	}
	
}

?>