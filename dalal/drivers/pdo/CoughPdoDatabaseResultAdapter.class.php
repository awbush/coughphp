<?php

/**
 * this wraps a PDOStatement object with Matt_Database style method names
 *
 * @package dalal
 * @author Lewis Zhang
 **/
class CoughPdoDatabaseResultAdapter extends CoughAbstractDatabaseResultAdapter
{	
	public static function retrieveByResult($result)
	{
		return new CoughPdoDatabaseResultAdapter($result);
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
	
	public function freeResult()
	{
		// hmm I need to set $this to null somehow
	}
	
} // END class CoughPdoDatabaseResultAdapter

?>