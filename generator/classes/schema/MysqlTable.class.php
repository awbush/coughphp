<?php

/**
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package default
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
class MysqlTable extends AbstractTable {
	
	public function loadColumns() {
		$sql = 'SHOW COLUMNS FROM `' . $this->tableName . '`';
		if (is_null($this->dbLink)) {
			$result = mysql_query($sql);
		} else {
			$result = mysql_query($sql, $this->dbLink);
		}
		if ( ! $result) {
			$this->generateError('Invalid query');
		}
		$this->columns = array();
		while ($record = mysql_fetch_assoc($result)) {
			$this->columns[$record['Field']] = new MysqlColumn($record, $this);
		}
	}
	
	protected function generateError($msg) {
		if (is_null($this->dbLink)) {
			throw new Exception($msg . ': ' . mysql_error());
		} else {
			throw new Exception($msg . ': ' . mysql_error($this->dbLink));
		}
	}
	
}

?>