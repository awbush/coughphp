<?php

/**
 * PDO database abstraction layer wrapper
 *
 * @package dalal
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
		if (isset($dbConfig['driver'])) {
			$driver = $dbConfig['driver'];
		} else {
			$driver = 'mysql';
		}
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
	
	/**
	 * selects the specified database for this connection
	 * TODO: Is there a better way to do this in PDO? e.g. like mysql_select_db instead of running a query.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function selectDb($databaseName)
	{
		$this->execute("USE `$databaseName`");
	}
	
}

?>