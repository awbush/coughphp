<?php

// Current Definitions

	protected function defineObjects() {
		parent::defineObjects();
	
		// Add to the defined objects like so:
	
		$this->objects['objectName'] = array(
			'class_name' => 'woc_ObjectName',
			'get_id_method' => 'getObjectNameID'
		);
	}
	protected function defineCollections() {
		parent::defineCollections();
	
		// Add to the defined collections like so:
	
		// For a one-to-many collection where there is no join table:
		$this->collections['document_Collection'] = array(
			'element_class' => 'woc_Document',
			'collection_class' => 'woc_Document_Collection',
			'collection_table' => 'document',
			'collection_key' => 'document_id',
			'relation_key' => 'product_id',
			'retired_column' => 'is_retired',
			'is_retired' => '1',
			'is_not_retired' => '0'
		);
	
		// For a many-to-many collection where there is a join table:
		$this->collections['subProduct_Collection'] = array(
			'element_class' => 'woc_SubProduct',
			'collection_class' => 'woc_SubProduct_Collection',
			'collection_table' => 'product',
			'collection_key' => 'product_id',
			'join_table' => 'product2product',
			'join_table_attr' => array(
				'retired_column' => 'is_retired',
				'is_retired' => '1',
				'is_not_retired' => '0'
			),
			'join_primary_key' => 'product2product_id',
			'relation_key' => 'child_id',
			'retired_column' => 'is_retired',
			'is_retired' => '1',
			'is_not_retired' => '0',
			'custom_check_function' => 'checkSubProduct_Collection'
		);
	
	}

// Current core Cough methods that use the defs

	protected function _loadObject($objectName) {
		$objectInfo = &$this->objectDefinitions[$objectName];
		$object = new $objectInfo['class_name']($this->$objectInfo['get_id_method']());
		$object->load();
		$this->objects[$objectName] = $object;
	}
	
	protected function _loadCollection($collectionName, $elementName = '', $sql = '', $orderBySQL = '') {
		$def =& $this->collectionDefinitions[$collectionName];
		
		$collection = new $def['collection_class']();
		
		if (isset($def['join_table'])) {
			$collection->setCollector($this, CoughCollection::MANY_TO_MANY, $def['join_table']);
			
			if (empty($sql) && $this->hasKeyId()) {
				$sql = '
					SELECT ' . $def['collection_table'] . '.*' . $this->getJoinSelectSql($collectionName) . '
					FROM ' .$def['join_table'] . '
					INNER JOIN ' . $def['collection_table'] . ' ON ' . $def['join_table'] . '.' . $def['collection_key']
						. ' = ' . $def['collection_table'] . '.' . $def['collection_key'] . '
					WHERE ' . $def['join_table'] . '.' . $def['relation_key'] . ' = ' . $this->db->quote($this->getKeyId());

				if (isset($def['retired_column']) && ! empty($def['retired_column'])) {
					$sql .= '
						AND ' . $def['collection_table'] . '.' . $def['retired_column'] . ' = ' . $this->db->quote($def['is_not_retired']);
				}

				if (isset($def['join_table_attr'])) {
					$joinAttr =& $def['join_table_attr'];
					if (isset($joinAttr['retired_column']) && ! empty($joinAttr['retired_column'])) {
						$sql .= '
							AND ' . $def['join_table'] . '.' . $joinAttr['retired_column'] . ' = ' . $this->db->quote($joinAttr['is_not_retired']);
					}
				}
			}
			
		} else {
			$collection->setCollector($this, CoughCollection::ONE_TO_MANY);
			
			if (empty($sql) && $this->hasKeyId()) {
				$sql = '
					SELECT *
					FROM ' . $def['collection_table'] . '
					WHERE ' . $def['relation_key'] . ' = ' . $this->db->quote($this->getKeyId());

				if (isset($def['retired_column']) && ! empty($def['retired_column'])) {
					$sql .= '
						AND ' . $def['retired_column'] . ' = ' . $this->db->quote($def['is_not_retired']);
				}
			}
			
		}
		
		if (empty($elementName)) {
			$elementName = $def['element_class'];
		}
		
		$collection->populateCollection($elementName, $sql, $orderBySQL);
		
		$this->collections[$collectionName] = $collection;
	}
	
// User still has to override in this way
	
	public function loadProduct_Collection() {
		$def =& $this->collectionDefinitions[$collectionName];
		
		$collection = new $def['collection_class']();
		$collection->setCollector($this, CoughCollection::MANY_TO_MANY, $def['join_table']);
		if ($this->hasKeyId()) {
			$sql = '
				SELECT ' . $def['collection_table'] . '.*' . $this->getJoinSelectSql($collectionName) . '
				FROM ' .$def['join_table'] . '
				INNER JOIN ' . $def['collection_table'] . ' ON ' . $def['join_table'] . '.' . $def['collection_key']
					. ' = ' . $def['collection_table'] . '.' . $def['collection_key'] . '
				WHERE ' . $def['join_table'] . '.' . $def['relation_key'] . ' = ' . $this->db->quote($this->getKeyId());

			if (isset($def['retired_column']) && ! empty($def['retired_column'])) {
				$sql .= '
					AND ' . $def['collection_table'] . '.' . $def['retired_column'] . ' = ' . $this->db->quote($def['is_not_retired']);
			}

			if (isset($def['join_table_attr'])) {
				$joinAttr =& $def['join_table_attr'];
				if (isset($joinAttr['retired_column']) && ! empty($joinAttr['retired_column'])) {
					$sql .= '
						AND ' . $def['join_table'] . '.' . $joinAttr['retired_column'] . ' = ' . $this->db->quote($joinAttr['is_not_retired']);
				}
			}
		}
		
		$elementName = $def['element_class'];
		$collection->populateCollection($elementName, $sql, $orderBySQL);
		$this->collections[$collectionName] = $collection;
		
	}
	
	protected function getJoinSelectSql($collectionName) {
		$def =& $this->collectionDefinitions[$collectionName];
		
		// Get extra join fields on-the-fly
		$joinFields = array();
		$sql = 'DESCRIBE '. $this->dbName . '.' . $def['join_table'];
		$result = $this->db->query($sql);
		while ($row = $result->getRow()) {
			$joinFieldName = $row['Field'];
			$joinFields[] = $def['join_table'] . '.' . $joinFieldName . ' AS `' . $def['join_table'] . '.' . $joinFieldName . '`';
		}
		$result->freeResult();
		if ( ! empty($joinFields)) {
			$joinFieldSql = ',' . implode(',', $joinFields);
		} else {
			$joinFieldSql = '';
		}
		return $joinFieldSql;
	}

	public function loadProduct_Collection() {
		$def =& $this->collectionDefinitions[$collectionName];
		
		$collection = new $def['collection_class']();
		$collection->setCollector($this, CoughCollection::ONE_TO_MANY);
		if ($this->hasKeyId()) {
			$sql = '
				SELECT *
				FROM ' . $def['collection_table'] . '
				WHERE ' . $def['relation_key'] . ' = ' . $this->db->quote($this->getKeyId());

			if (isset($def['retired_column']) && ! empty($def['retired_column'])) {
				$sql .= '
					AND ' . $def['retired_column'] . ' = ' . $this->db->quote($def['is_not_retired']);
			}
		}
		
		$elementName = $def['element_class'];
		$collection->populateCollection($elementName, $sql, $orderBySQL);
		$this->collections[$collectionName] = $collection;
		
	}


// Proposal for "light" defs (we really wouldn't have to have any...)

	protected function defineObjects() {
		parent::defineObjects();
		$this->objects['objectName'] = array(
			'class_name' => 'ObjectName' // needed for inflateObject function...
		);
	}
	
	protected function defineCollections() {
		parent::defineCollections();
		$this->collections['document'] = true; // one-to-many example
		$this->collections['subProduct'] = true; // many-to-many example
	}

// All these core Cough methods can now be removed


// These methods would be generated, and the user now has a better starting point for overriding the methods.

	public function loadProduct_Collection() {
	
		$collection = new Product_Collection();
		$collection->setCollector($this, CoughCollection::ONE_TO_MANY);
		if ($this->hasKeyId()) {
			$dummyProduct = new Product();
			$sql = $dummyProduct->getLoadSqlWithoutWhere();
			$sql . = ' WHERE os_id = 1 AND is_retired = 0';
			$collection->populateCollection($elementClassName = '', $sql, $orderBySQL = '');
			// In this example, getLoadSqlWithoutWhere() probably return: {
			$sql = '
				SELECT
					product.*,
					manufacturer.* AS `manufacturer.column_name`,
					title.* AS `title.column_name`,
				FROM
					product
					INNER JOIN manufacturer USING (manufacturer_id)
					INNER JOIN title USING (title_id)
			';
			// }
		}
	
		$this->collections[$collectionName] = $collection;
	}


	protected function loadProduct_Object() {
		$product = new Product($this->getProductId());
		// $product->load();
		$this->objects['product'] = $product;
	}


?>