<?php

/**
 * Provides a way to insert SQL functions into queries.
 * 
 * The As_Database::quote() method will not quote these, and instead will
 * rely on the getString() method.
 *
 * @package as_database
 **/
class As_SqlFunction {
	protected $sqlFunction;
	public function __construct($sqlFunction) {
		$this->sqlFunction = $sqlFunction;
	}
	public function __toString() {
		return $this->sqlFunction;
	}
	public function getString() {
		return $this->sqlFunction;
	}
}

?>