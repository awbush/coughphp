<?php

/**
 * This interface must be implemented in the concrete/generated classes.
 * 
 * The CoughGenerator takes care of it, but if you write your own classes by hand
 * make sure to implement CoughObjectStaticInterface.
 *
 * @author Anthony Bush
 * @package cough
 **/
interface CoughObjectStaticInterface
{
	public static function getDb();
	public static function getDbName();
	public static function getTableName();
	public static function getPkFieldNames();
	public static function getLoadSql();
	public static function constructByFields($hash);
	public static function constructByKey($idOrHash);
	public static function constructBySql($sql);
}

?>