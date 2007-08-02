<?php

/**
 * Override this function if you need to specify different foreign key ->
 * primary key relationships
 *
 * @return string primary key to look for
 * @author Anthony Bush
 **/
protected function getPrimaryKeyFromForeignKey($foreignKey) {
	if (substr($foreignKey, 0, 8) == 'default_') {
		return substr($foreignKey, 8);
	} else if (substr($foreignKey, 0, 8) == 'primary_') {
		return substr($foreignKey, 8);
	}
	// For now, don't do any special parent/child stuff... TODO: Add parent/child handling
	// else if (substr($foreignKey, 0, 7) == 'parent_') {
	// 	return $this->tableName . $this->idSuffix;
	// } else if (substr($foreignKey, 0, 6) == 'child_') {
	// 	return $this->tableName . $this->idSuffix;
	// }
	else {
		return $foreignKey;
	}
}


/**
 * Gets the table name that has the given key as a primary key.
 *
 * Try giving it a foreign key. It will give you back the table name
 * that has it has the primary key.
 *
 * @return string the table name
 * @author Anthony Bush
 **/
protected function getTableWhosePrimaryKeyIs($keyName) {
	foreach ($this->tables as $tableName => $table) {
		if ($table['primary_key']['db_column_name'] == $keyName) {
			return $tableName;
		}
	}
	return '';
}



?>