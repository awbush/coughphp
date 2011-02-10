<?php
class DerivedFieldBook extends Book
{
	protected $derivedFieldDefinitions = array(
		'rating' => true,
	);
	
	public function setRating($rating)
	{
		$this->setDerivedField('rating', $rating);
	}
}
