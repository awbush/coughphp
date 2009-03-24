<?php

/**
 * Strategy that just toggles an "is_retired" column.
 *
 * @author Anthony Bush
 * @since 1.4
 * @see http://coughphp.com/blog/2008/09/04/modules-out-of-the-box-event-logging-for-coughphp/
 * @see http://coughphp.com/blog/2008/09/24/minutes-coughphp-developers-meeting/
 **/
class CoughDeletionStrategyRetire extends CoughDeletionStrategy
{
	public function delete(CoughObject $object)
	{
		$object->setIsRetired(true);
		return $object->save();
	}
}

?>