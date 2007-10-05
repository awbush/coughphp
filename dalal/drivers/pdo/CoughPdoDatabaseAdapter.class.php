<?php

/**
 * PDO database abstraction layer wrapper
 *
 * @package default
 * @author Lewis Zhang
 **/
class CoughPdoDatabaseAdapter extends CoughAbstractDatabaseAdapter
{	
	/**
	 * creates a new PDO connection
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDbConfig($dbConfig)
	{
		$driver = $dbConfig['driver'];
		$host = $dbConfig['host'];
		$username = $dbConfig['user'];
		$password = $dbConfig['pass'];
		$database = $dbConfig['db_name'];
		
		return new CoughPdoDatabaseAdapter(new PDO("$driver:host=$host;dbname=$database", $username, $password));
	}
	
	public function query($sql)
	{
		$result = $this->db->query($sql);
		if (is_object($result)) {
			return CoughPdoDatabaseResultAdapter::retrieveByResult($result);
		}
		else {
			return false;
		}
	}
	
	public function execute($sql)
	{
		return $this->db->exec($sql);
	}
	
	public function getLastInsertId()
	{
		return $this->db->lastInsertId();
	}
	
	public function dbQuote($string)
	{
		return $this->db->quote($string);
	}
}

?>