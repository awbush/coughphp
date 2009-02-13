<?php

class As_DeleteQuery extends As_Query
{
	protected $tableName = '';
	protected $where = array();
	
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}
	
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
		$sql = 'DELETE FROM ' . $this->db->backtick($this->tableName);
		
		// Add WHERE (optional)
		if (!empty($this->where))
		{
			$sql .= ' WHERE ';
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