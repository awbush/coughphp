<?php

abstract class CoughDeletionStrategy
{
	/**
	 * (Maintenance free) Factory method for including and constructing a deletion strategy.
	 * 
	 * Example usage inside a CoughObject's delete() method:
	 * <code>
	 * return CoughDeletionStrategy::constructByType('Delete')->delete($this);
	 * </code>
	 * 
	 * The above is generated for you when we set the deletion_strategy config
	 * setting in cough_generator.inc.php
	 * 
	 * @return CoughDeletionStrategy
	 * @author Anthony Bush
	 * @since 1.4
	 **/
	public static function constructByType($strategyType)
	{
		$className = 'CoughDeletionStrategy' . $strategyType;
		require_once(dirname(__FILE__) . '/deletion_strategies/' . $className . '.class.php');
		return new $className();
	}
	
	/**
	 * "Delete" a CoughObject.
	 * 
	 * @param CoughObject $obj
	 * @return boolean true on success, false on failure
	 **/
	abstract public function delete(CoughObject $obj);
}

?>