<?php
class NoPkBook extends Book
{
	protected static $pkFieldNames = array();
	
	public static function constructByKey($idOrHash, $forPhp5Strict = '')
	{
		return CoughObject::constructByKey($idOrHash, 'NoPkBook');
	}
	
	public static function getPkFieldNames() {
		return NoPkBook::$pkFieldNames;
	}
}
