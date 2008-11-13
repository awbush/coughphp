<?php

/**
 * A simple SQL query object for SELECT statements.
 * 
 * Usage of this class in Cough allows for sharing base SQL so that less SQL code
 * is copied and pasted amongst classes.
 * 
 * For example, if there is a Product object which has complicated SELECT SQL
 * that joins into other tables, pulls extra data in the SELECT clause, includes
 * a GROUP BY, and has default WHERE criteria like "is_retired = 0", then by
 * using this you don't have to copy all those common parts into the all the
 * collection static methods.  Instead, they can share the base SQL and just add
 * what they need.  For example, to pull a single product one might do:
 * 
 *     <code>
 *     $sql = Product::getLoadSql();
 *     $sql->addWhere(array('product_id' => $productId));
 *     echo $sql;
 *     </code>
 * 
 * Another example might be pulling all products that have the same manufacturer ID:
 * 
 *     <code>
 *     Product_Collection::getProductsByManufacturer($manufId) {
 *         $sql = Product::getLoadSql();
 *         $sql->addWhere(array('manufacturer_id' => $manufId));
 *         $collection = new Product_Collection();
 *         $collection->loadBySql($sql);
 *         return $collection;
 *     }
 *     </code>
 * 
 * @package as_query
 * @author Anthony Bush
 **/
class As_SelectQuery extends As_Query
{
	protected $select = array();
	protected $selectOptions = array();
	protected $from = array();
	protected $where = array();
	protected $groupBy = array();
	protected $having = array();
	protected $orderBy = array();
	protected $limit = '';
	protected $offset = '';
	
	// Getters
	
	public function getSelect()
	{
		return $this->select;
	}
	
	public function getSelectOptions()
	{
		return $this->select;
	}
	
	public function getFrom()
	{
		return $this->from;
	}
	
	public function getWhere()
	{
		return $this->where;
	}
	
	public function getGroupBy()
	{
		return $this->groupBy;
	}
	
	public function getHaving()
	{
		return $this->having;
	}
	
	public function getOrderBy()
	{
		return $this->orderBy;
	}
	
	public function getLimit()
	{
		return $this->limit;
	}
	
	public function getOffset()
	{
		return $this->offset;
	}
	
	// Setters
	
	public function setSelect($select)
	{
		$this->select = $select;
	}
	
	public function addSelect($select)
	{
		$this->add($select, $this->select, ",\n\t");
	}
	
	/**
	 * Add optional select options, e.g. SQL_CALC_FOUND_ROWS, SQL_NO_CACHE
	 * 
	 * @param string $selectOption
	 * @return void
	 **/
	public function addSelectOption($selectOption)
	{
		$this->selectOptions[$selectOption] = true;
	}
	
	public function setFrom($from)
	{
		$this->from = $from;
	}
	
	public function addFrom($from)
	{
		$this->add($from, $this->from, "\n\t");
	}
	
	public function setWhere($where)
	{
		$this->where = $this->remapCriteria($where);
	}
	
	public function addWhere($where)
	{
		$this->add($this->remapCriteria($where), $this->where, "\n\tAND ");
	}
	
	public function setGroupBy($groupBy)
	{
		$this->groupBy = $groupBy;
	}
	
	public function addGroupBy($groupBy)
	{
		$this->add($groupBy, $this->groupBy, ",\n\t");
	}
	
	public function setHaving($having)
	{
		$this->having = $this->remapCriteria($having);
	}
	
	public function addHaving($having)
	{
		$this->add($this->remapCriteria($having), $this->having, "\n\tAND ");
	}
	
	public function setOrderBy($orderBy)
	{
		$this->orderBy = $orderBy;
	}
	
	public function addOrderBy($orderBy)
	{
		$this->add($orderBy, $this->orderBy, ",\n\t");
	}
	
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}
	
	public function setOffset($offset)
	{
		$this->offset = $offset;
	}
	
	public function getString()
	{
		$sql = '';
		if (!empty($this->select) && !empty($this->from))
		{
			// Add SELECT (required)
			$sql .= "SELECT \n\t";
			
			// Add select options (optional)
			if (!empty($this->selectOptions)) {
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
				$sql .= "\nGROUP BY \n\t";
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
			
			// Add LIMIT and OFFSET (optional)
			if (!empty($this->limit))
			{
				$sql .= "\nLIMIT " . $this->limit;
				if (!empty($this->offset))
				{
					$sql .= ' OFFSET ' . $this->offset;
				}
			}
		}
		return $sql;
	}
	
	/**
	 * Hack for versions of PHP that do not work with clone
	 *
	 * @return As_SelectQuery
	 * @author Anthony Bush
	 * @since 2008-03-17
	 **/
	public function getClone()
	{
		$sql = new As_SelectQuery($this->db);
		$sql->setSelect($this->getSelect());
		$sql->setFrom($this->getFrom());
		$sql->setWhere($this->getWhere());
		$sql->setGroupBy($this->getGroupBy());
		$sql->setHaving($this->getHaving());
		$sql->setOrderBy($this->getOrderBy());
		$sql->setLimit($this->getLimit());
		$sql->setOffset($this->getOffset());
		return $sql;
	}
}

?>
