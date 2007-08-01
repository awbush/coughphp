<?php

class MysqlDatabase extends AbstractDatabase {
	
	public function loadTable($tableName) {
		$this->tables[$tableName] = new MysqlTable($tableName, $this->dbLink, $this);
		$this->tables[$tableName]->loadColumns();
	}
	
	public function getTableNames() {
		$sql = 'SHOW TABLES';
		if (is_null($this->dbLink)) {
			$result = mysql_query($sql);
		} else {
			$result = mysql_query($sql, $this->dbLink);
		}
		if ( ! $result) {
			$this->generateError('Invalid query');
		}
		$values = array();
		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$values[] = $record[0];
		}
		return $values;
	}
	
	public function selectDb($dbName) {
		$db_selected = mysql_select_db($dbName, $this->dbLink);
		if ( ! $db_selected) {
			$this->generateError("Can't select database");
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