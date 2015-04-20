---
title: Using Transactions - CoughPHP
---

Using Transactions
==================

Transaction usage is done at the DB layer, so it can differ based on which database abstraction layer you choose to use with CoughPHP.  If you are using the one included with CoughPHP (as_database), then you can use transactions like so:

	$db = ConcreteCoughObject::getDb();
	$db->startTransaction();
	
	try
	{
		// do save() calls, raw queries, etc.
		
		$db->commit();
	}
	catch (Exception $e)
	{
		$db->rollback();
	}

Remember to make sure that the tables you are using in the transaction are InnoDB and not MyISAM because MyISAM does not support transactions.

Reference:

* [MySQL 5.0 Reference Manual :: 1.8.5.2 Transactions and Atomic Operations](http://dev.mysql.com/doc/refman/5.0/en/ansi-diff-transactions.html)
