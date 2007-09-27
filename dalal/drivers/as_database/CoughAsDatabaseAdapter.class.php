<?php

/**
 * Matt Database abstraction layer wrapper
 *
 * @package default
 * @author Lewis Zhang
 **/
class Lz_MattDatabase extends Lz_Database
{	
	/**
	 * creates a new MattDatabase connection from a DSN
	 * NOTE: this may or may not apply after the DatabaseFactory changes to include Lz_Database
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDsn($dsn)
	{
		// driver is irrelavent, as MattDatabase only supports mysql
		$driver = $dsn['driver'];
		
		$host = $dsn['host'];
		$username = $dsn['username'];
		$password = $dsn['password'];
		$database = $dsn['database'];
		
		return new Lz_MattDatabase(new Database($database, $host, $username, $password));
	}
	
	public function query($sql)
	{
		$result = $this->db->query($sql);
		return Lz_MattDatabaseResult::retrieveByResult($result);
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
}

?>