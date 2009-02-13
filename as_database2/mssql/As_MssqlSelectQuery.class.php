<?php

class As_MssqlSelectQuery extends As_SelectQuery
{
	/**
	 * Overridden to handle LIMIT/OFFSET
	 * 
	 * @return string
	 * @throws As_DatabaseException
	 **/
	public function getString()
	{
		$sql = '';
		if (!empty($this->select) && !empty($this->from))
		{
			// Add SELECT (required)
			$sql .= "SELECT\n\t";
			
			// Add LIMIT (optional)
			if (!empty($this->limit))
			{
				$sql .= "TOP " . (int)$this->limit . "\n\t";
			}
			
			// OFFSET not supported
			if (!empty($this->offset))
			{
				throw new As_DatabaseException('OFFSET is not supported by MSSQL drivers');
			}
			
			// Add select options (optional)
			if (!empty($this->selectOptions))
			{
				$sql .= implode(' ', array_keys($this->selectOptions)) . "\n\t";
			}
			
			if (is_array($this->select))
			{
				$sql .= implode(",\n\t", $this->select);
			}
			else
			{
				$sql .= $this->select;
			}
			
			// Add FROM (required)
			$sql .= "\nFROM\n\t";
			if (is_array($this->from))
			{
				$sql .= implode("\n\t", $this->from);
			}
			else
			{
				$sql .= $this->from;
			}
			
			// Add WHERE (optional)
			if (!empty($this->where))
			{
				$sql .= "\nWHERE\n\t";
				if (is_array($this->where))
				{
					$sql .= implode("\n\tAND ", $this->where);
				}
				else
				{
					$sql .= $this->where;
				}
			}
			
			// Add GROUP BY (optional)
			if (!empty($this->groupBy))
			{
				$sql .= "\nGROUP BY\n\t";
				if (is_array($this->groupBy))
				{
					$sql .= implode(",\n\t", $this->groupBy);
				}
				else
				{
					$sql .= $this->groupBy;
				}
				
				// Add HAVING (optional)
				if (!empty($this->having))
				{
					$sql .= "\nHAVING\n\t";
					if (is_array($this->having))
					{
						$sql .= implode("\n\tAND ", $this->having);
					}
					else
					{
						$sql .= $this->having;
					}
				}
			}
			
			// Add ORDER BY (optional)
			if (!empty($this->orderBy))
			{
				$sql .= "\nORDER BY \n\t";
				if (is_array($this->orderBy))
				{
					$sql .= implode(",\n\t", $this->orderBy);
				}
				else
				{
					$sql .= $this->orderBy;
				}
			}
		}
		return $sql;
	}
}

?>