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
	 * @return CoughPdoDatabaseAdapter
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDbConfig($dbConfig)
	{
		$driver = $dbConfig['driver'];
		$host = $dbConfig['host'];
		$username = $dbConfig['user'];
		$password = $dbConfig['pass'];
		$database = $dbConfig['db_name'];
		
		$db = new PDO("$driver:host=$host;dbname=$database", $username, $password);
		$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		
		return new CoughPdoDatabaseAdapter($db);
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
	
	/**
	 * in PDO escaping is done by the quote() method (dbQuote() in the adapter)
	 * TODO: deprecate escape() calls, and instead just use quote() all over
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function escape($string)
	{
		// hack to return a string that is escaped but not quoted
		$quotedAndEscapedString = $this->dbQuote($string);
		return substr($quotedAndEscapedString, 1, strlen($quotedAndEscapedString) - 2);
	}
}

?>