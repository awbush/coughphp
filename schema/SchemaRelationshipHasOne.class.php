<?php

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