<?php

/**
 * database abstraction layer abstraction layer
 *
 * @package default
 * @author Lewis Zhang
 **/
abstract class Lz_Database
{
	/**
	 * database abstraction layer object
	 * ex: PDO, AdoDB, MDB, Creole, etc
	 *
	 **/
	protected $db = null;
	
	/**
	 * sets the new database object
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	protected function __construct($db)
	{
		$this->db = $db;
	}
	
	/**
	 * returns a database object
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public static function retrieveByDsn($dsn)
	{
		switch ($dsn['dbLayer']) {
			case 'pdo':
				return Lz_PdoDatabase::retrieveByDsn($dsn);
				break;
			case 'matt_database':
				return Lz_MattDatabase::retrieveByDsn($dsn);
				break;
		}
	}
	
	public function showErrors()
	{
		$errors = print_r($this->getErrors(), true);
		echo '<pre>';
		throw new Exception("SQL Error:\n$errors", 1);
		echo '</pre>';
	}
	
	abstract public function getErrors();
		
	abstract public function query($sql);
	
	abstract public function execute($sql);
} // END class Lz_Database

?>