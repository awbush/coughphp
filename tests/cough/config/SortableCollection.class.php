<?php

class SortableCollection extends CoughCollection
{
	protected $elementClassName = 'SortableElement';
	
	public function getArrayKeys()
	{
		$keys = array();
		foreach ($this as $key => $element) {
			$keys[] = $key;
		}
		return $keys;
	}
}

?>