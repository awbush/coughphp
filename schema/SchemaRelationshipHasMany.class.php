<?php

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