---
title: Getting Updated Fields After Creation - CoughPHP
---

Getting Updated Fields After Creation
=====================================

Cough does not re-check the object's database record after inserting. This means fields like creation_datetime which may be filled in automatically by the database server are not updated in the object scope (the primary key id is an exception and is updated when possible). Like most things in CoughPHP, you can change this behavior by overriding a method.

In this case, we need only override the insert() method:

	protected function insert()
	{
		if (parent::insert())
		{
			$db = self::getDb();
			$db->selectDb(self::getDbName());
			
			$sql = new As_SelectQuery($db);
			$sql->setSelect('*');
			$sql->setFrom(self::getTableName());
			$sql->setWhere($this->getPk());
			
			$result = $db->query($sql);
			$this->inflate($result->getRow());
			return true;
		}
		else
		{
			return false;
		}
	}
