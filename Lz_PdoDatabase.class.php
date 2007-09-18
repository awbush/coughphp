<?php

/**
 * PDO database abstraction layer wrapper
 *
 * @package default
 * @author Lewis Zhang
 **/
class Lz_PdoDatabase extends Lz_Database
{	
	/**
	 * creates a new PDO connection
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDsn($dsn)
	{
		$driver = $dsn['driver'];
		$host = $dsn['host'];
		$user = $dsn['username'];
		$password = $dsn['password'];
		$database = $dsn['database'];
		
		return new Lz_PdoDatabase(new PDO("$driver:host=$host;dbname=$database", $user, $password));
	}
	
	public function getErrors()
	{
		return $this->db->errorInfo();
	}
	
	public function query($sql)
	{
		$result = $this->db->query($sql);
		if (is_object($result)) {
			return Lz_PdoDatabaseResult::retrieveByResult($result);
		}
		else {
			$this->showErrors();
		}
	}
	
	public function execute($sql)
	{
		
	}
}

?>