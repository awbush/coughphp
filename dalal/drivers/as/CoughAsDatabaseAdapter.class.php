<?php

/**
 * Matt Database abstraction layer wrapper
 *
 * @package default
 * @author Lewis Zhang
 **/
class CoughAsDatabaseAdapter extends CoughAbstractDatabaseAdapter
{	
	/**
	 * creates a new MattDatabase connection from a DSN
	 * NOTE: this may or may not apply after the DatabaseFactory changes to include CoughAbstractDatabaseAdapter
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDbConfig($dbConfig)
	{
		// driver is irrelavent, as MattDatabase only supports mysql
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
		// TODO: MattDatabase always returns an object, so we need to find out how to return false when the query failed
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
	 * NOTE: This is mysql specific
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function escape($string)
	{
		if (get_magic_quotes_gpc()) {
			$string = stripslashes($string);
		}
		return mysql_real_escape_string($string, $this->db->connection);
	}
}

?>