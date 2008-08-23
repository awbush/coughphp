<?php
/**
 * Any database result object that Cough uses must implement this interface.
 *
 * @package cough
 **/
interface CoughDatabaseResultInterface
{
	public function getRow();
}
?>