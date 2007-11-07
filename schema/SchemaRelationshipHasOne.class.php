<?php

/**
 * SchemaRelationshipHasOne adds methods to pull it's "has many"
 * counterpart.
 *
 * @package schema
 * @author Anthony Bush
 **/
class SchemaRelationshipHasOne extends SchemaRelationship {
	protected $hasManyRelationship = null;

	public function getHasManyRelationship() {
		return $this->hasManyRelationship;
	}

	public function setHasManyRelationship($hasManyRelationship) {
		$this->hasManyRelationship = $hasManyRelationship;
	}
}

?>