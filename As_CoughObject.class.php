<?php

/**
 * Extends the new CoughObject and provides backwards compatibility to some of the old methods...
 *
 * @package default
 * @author Anthony Bush
 **/
class As_CoughObject {
	
	/**
	 * Retrieves the object's data from the database, loading it into memory.
	 * 
	 * @return boolean - whether or not check was able to find a record in the database.
	 * @author Anthony Bush
	 **/
	public function check() {
		return $this->load();
	}
	
	/**
	 * Returns the current SQL statement that the {@link check()} method should
	 * run.
	 * 
	 * Override this in sub classes for custom SQL.
	 *
	 * @return mixed - string of SQL or empty string if no SQL to run.
	 * @author Anthony Bush
	 **/
	protected function getCheckSql() {
		return $this->getLoadSql();
	}
	
	/**
	 * Provides a way to `check` by an array of "key" => "value" pairs.
	 *
	 * @param array $where - an array of "key" => "value" pairs to search for
	 * @param boolean $additionalSql - add ORDER BYs and LIMITs here.
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkByCriteria($where = array(), $additionalSql = '') {
		return $this->loadByCriteria($where, $additionalSql);
	}
	
	/**
	 * Provides a way to `check` by custom SQL.
	 *
	 * @param string $sql - custom SQL to use during the check
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkBySql($sql) {
		return $this->loadBySql($sql);
	}

	/**
	 * Set whether or not a check returned a result from the database.
	 *
	 * @param boolean $value - true if check returned a result, false if not.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function setCheckReturnedResult($value) {
		$this->setIsLoaded($value);
	}
	
	/**
	 * Get whether or not a check returned a result from the database.
	 *
	 * Note that there is no `getCheckReturnedResult` function, as this is it.
	 *
	 * @return boolean - true if check returned a result, false if not.
	 * @author Anthony Bush
	 **/
	public function didCheckReturnResult() {
		return $this->isLoaded();
	}
	
} // END class As_CoughObject

?>