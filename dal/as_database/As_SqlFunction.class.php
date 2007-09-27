<?php

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