<?php

/**
 * Strategy that actually deletes the record from the database (if it has a key).
 *
 * @author Anthony Bush
 * @since 1.4
 **/
class CoughDeletionStrategyDelete extends CoughDeletionStrategy
{
	public function delete(CoughObject $object)
	{
		if ($object->hasKeyId())
		{
			$db = $object->getDb();
			$db->selectDb($object->getDbName());
			
			$query = $db->getDeleteQuery();
			$query->setTableName($object->getTableName());
			$query->setWhere($object->getPk());
			$query->run();
		}
		return true;
	}
}

?>