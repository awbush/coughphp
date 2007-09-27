<?php

/**
 * this wraps a DatabaseResult object with a Cough compliant result object interface
 * NOTE: this is nearly identical to DatabaseResult
 *
 * @package default
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
		return mysql_fetch_assoc($this->result);
	}

	public function getRows()
	{
		$rows = array();
		while ($row = mysql_fetch_assoc($this->result)) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function getNumRows()
	{
		return mysql_num_rows($this->result);
	}

	public function result($rowNum)
	{
		return mysql_result($this->result, $rowNum);
	}

	public function freeResult()
	{
		return mysql_free_result($this->result);
	}
	
} // END class CoughAsDatabaseResultAdapter

?>