<?php

/**
 * Insert specific query
 *
 * @package as_query
 * @author Anthony Bush
 **/
class As_InsertQuery extends As_Query
{
	protected $tableName = '';
	protected $fields = array();
	
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}
	
	public function setFields($fields)
	{
		$this->fields = $fields;
	}
	
	public function getString()
	{
		if (empty($this->fields))
		{
			return 'INSERT INTO `' . $this->tableName . '` VALUES ()';
		}
		
		$sql = 'INSERT INTO `' . $this->tableName . '` ';
		
		$fieldList = '( ';
		$valueList = '( ';
		foreach ($this->fields as $key => $value) {
			$fieldList .= '`' . $key . '`,';
			$valueList .= $this->db->quote($value) . ',';
		}
		$valueList = substr_replace($valueList,'',-1) . ') '; // faster than having done an implode
		$fieldList = substr_replace($fieldList,'',-1) . ') '; // faster than having done an implode
		$sql .= $fieldList . ' VALUES ' . $valueList;
				
		return $sql;
	}
}

?>