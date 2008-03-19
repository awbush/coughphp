<?php
/**
 * Any database result object that Cough uses must implement this interface.
 *
 * @package dal
 **/
interface CoughDatabaseResultInterface
{
	public function getRow();
}
?>