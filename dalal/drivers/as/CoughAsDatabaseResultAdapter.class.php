<?php

/**
 * this wraps a DatabaseResult object with a Cough compliant result object interface
 * NOTE: this is nearly identical to DatabaseResult
 *
 * @package dalal
 * @author Lewis Zhang
 **/
class CoughAsDatabaseResultAdapter extends CoughAbstractDatabaseResultAdapter
{	
	public static function retrieveByResult($result)
	{
		return new CoughAsDatabaseResultAdapter($result);
	}

	public function getRow()
	{
		return $this->result->getRow();
	}

	public function getRows()
	{
		$rows = array();
		while ($row = $this->result->getRow()) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function getNumRows()
	{
		return $this->result->getNumRows();
	}

	public function result($rowNum)
	{
		return $this->result->result($rowNum);
	}

	public function freeResult()
	{
		return $this->result->freeResult();
	}
	
} // END class CoughAsDatabaseResultAdapter

?>