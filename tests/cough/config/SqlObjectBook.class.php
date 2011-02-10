<?php
class SqlObjectBook extends Book
{
	public static function constructByKey($idOrHash, $forPhp5Strict = '')
	{
		return CoughObject::constructByKey($idOrHash, 'SqlObjectBook');
	}
	
	public static function getLoadSql()
	{
		$tableName = Book::getTableName();
		$sql = new As_SelectQuery(Book::getDb());
		$sql->setSelect('
				`' . $tableName . '`.*
		');
		$sql->setFrom('
				`' . Book::getDbName() . '`.`' . $tableName . '`
		');
		return $sql;
	}
}
