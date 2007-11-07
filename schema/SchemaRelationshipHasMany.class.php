<?php

/**
 * SchemaRelationshipHasMany adds methods to pull it's "has one"
 * counterpart.
 *
 * @package schema
 * @author Anthony Bush
 **/
class SchemaRelationshipHasMany extends SchemaRelationship {
	
	protected $hasOneRelationship = null;

	public function getHasOneRelationship() {
		return $this->hasOneRelationship;
	}

	public function setHasOneRelationship($hasOneRelationship) {
		$this->hasOneRelationship = $hasOneRelationship;
	}
}

?>