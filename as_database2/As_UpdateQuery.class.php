<?php

/**
 * Update specific query
 *
 * @package as_query
 * @author Anthony Bush
 **/
class As_UpdateQuery extends As_InsertQuery
{
	protected $where = array();
	
	public function setWhere($where)
	{
		$this->where = $this->remapCriteria($where);
	}
	
	public function addWhere($where)
	{
		$this->add($this->remapCriteria($where), $this->where, ' AND ');
	}
	
	public function getString()
	{
		if (empty($this->fields))
		{
			return '';
		}
		
		$sql = 'UPDATE `' . $this->tableName . '` SET ';
		
		// Add SET list
		$setList = '';
		foreach($this->fields as $key => $value) {
			$setList .= '`' . $key . '` = ' . $this->db->quote($value) . ',';
		}
		$sql .= substr_replace($setList,'',-1) . ' '; // faster than having done an implode
		
		// Add WHERE (optional)
		if (!empty($this->where))
		{
			$sql .= 'WHERE ';
			if (is_array($this->where))
			{
				$sql .= implode(' AND ', $this->where);
			}
			else
			{
				$sql .= $this->where;
			}
		}
		
		return $sql;
	}
}

?>