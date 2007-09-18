<?php

/**
 * this wraps a PDOStatement object with Matt_Database style method names
 *
 * @package default
 * @author Lewis Zhang
 **/
class Lz_PdoDatabaseResult extends Lz_DatabaseResult
{	
	public static function retrieveByResult($result)
	{
		return new Lz_PdoDatabaseResult($result);
	}
	
	public function getRow()
	{
		return $this->result->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getRows()
	{
		return $this->result->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getNumRows()
	{
		return $this->result->rowCount();
	}
	
	// TODO: this is broken
	public function result($rowNum)
	{
		return $this->result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $rowNum);
	}
} // END class Lz_DatabaseResult

?>