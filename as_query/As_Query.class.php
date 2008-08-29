<?php

/**
 * Base class used by the insert, update, select classes.
 *
 * @package as_query
 * @author Anthony Bush
 **/
class As_Query
{
	protected $db = null;
	
	/**
	 * Pass in a database object that provides a quote() method.
	 * Will be used to quote WHERE conditions.
	 **/
	public function __construct($db = null)
	{
		$this->setDb($db);
	}
	
	public function setDb($db)
	{
		$this->db = $db;
	}
	
	public function __toString()
	{
		return $this->getString();
	}
	
	// Override this one in sub query classes
	public function getString()
	{
		return '';
	}
	
	public function run()
	{
		return $this->db->query($this->getString());
	}
	
	public function execute()
	{
		return $this->db->execute($this->getString());
	}
	
	/**
	 * Adds $stringOrArray to $dest using $delin as a separator.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function add($stringOrArray, &$dest, $delim)
	{
		if (is_array($dest))
		{
			if (is_array($stringOrArray))
			{
				$dest = array_merge($dest, $stringOrArray);
			}
			else
			{
				$dest[] = $stringOrArray;
			}
		}
		else
		{
			if (is_array($stringOrArray))
			{
				$dest .= $delim . implode($delim, $stringOrArray);
			}
			else
			{
				$dest .= $delim . $stringOrArray;
			}
		}
	}
	
	/**
	 * Re-map the given array to allow for multiple formats.
	 * 
	 * Format 1: Pre-quoted values with pre-filled equators:
	 *     
	 *     array(
	 *         'some_field = "some_value"',
	 *         'some_other_field = "some_other_value"'
	 *     )
	 * 
	 * Format 2: Non-quoted values with no equators:
	 * 
	 *     array(
	 *         'some_field' => 'some_value',
	 *         'some_other_field' => 'some_other_value'
	 *     )
	 * 
	 * Format 3: Mixed of the above two.
	 * 
	 *     array(
	 *         'some_field = "some_value"',
	 *         'some_other_field' => 'some_other_value'
	 *     )
	 * 
	 * @author Anthony Bush
	 **/
	protected function remapCriteria($criteria)
	{
		if (is_array($criteria))
		{
			$newCriteria = array();
			foreach ($criteria as $key => $value)
			{
				if (is_numeric($key))
				{
					$newCriteria[] = $value;
				}
				else
				{
					if (!is_object($this->db)) {
						throw new Exception('Must construct As_Query with a database object if relying on it to do quoting.');
					}
					$newCriteria[] = $key . $this->getEqualityOperatorFromValue($value) . $this->db->quote($value);
				}
			}
			return $newCriteria;
		}
		else
		{
			return $criteria;
		}
	}
	
	/**
	 * builds a WHERE clause from the supplied array
	 *
	 * @return string
	 * @author Lewis Zhang, Anthony Bush
	 * @throws Exception
	 **/
	public function buildWhereSql($where)
	{
		if (empty($where))
		{
			return '';
		}
		
		if (!is_object($this->db)) {
			throw new Exception('Must construct As_Query with a database object if building WHERE SQL.');
		}
		
		$clauseSql = '';
		foreach ($where as $fieldName => $fieldValue) {
			$clauseSql .= $fieldName . $this->getEqualityOperatorFromValue($fieldValue) . $this->db->quote($fieldValue) . ' AND ';
		}
		$clauseSql = substr_replace($clauseSql, '', -5);
		
		return $clauseSql;
	}
	
	/**
	 * returns the proper equality operator from the value being tested
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function getEqualityOperatorFromValue($fieldValue)
	{
		if ($fieldValue === null) {
			return ' IS ';
		}
		else {
			return ' = ';
		}
	}
	
	// Factory methods
	
	public static function getSelectQuery($db)
	{
		// We could check if the db type is MS SQL and return a different object that supports TOP instead of LIMIT, e.g.
		return new As_SelectQuery($db);
	}
	
	public static function getInsertQuery($db)
	{
		// We could check if the db type is MS SQL and return a different object that supports TOP instead of LIMIT, e.g.
		return new As_InsertQuery($db);
	}
	
	public static function getUpdateQuery($db)
	{
		// We could check if the db type is MS SQL and return a different object that supports TOP instead of LIMIT, e.g.
		return new As_UpdateQuery($db);
	}
}

?>
