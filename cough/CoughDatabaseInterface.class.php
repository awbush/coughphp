<?php
/**
 * Any database object that Cough uses must implement this interface.
 *
 * @package cough
 **/
interface CoughDatabaseInterface
{
	public static function constructByConfig($dbConfig);
	public function query($sql);
	public function execute($sql);
	public function getLastInsertId();
	public function quote($sql);
	public function selectDb($dbName);
}
?>