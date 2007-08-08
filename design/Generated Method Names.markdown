Generated Method Names
======================

Generated method names are designed to meet several goals:

1. Avoid naming conflicts.
	* Separate field, object, and collection accessors by prefixing '_Object' and '_Collection'
	* Allow "object name" overriding at the schema level.
	
		For example, if a `product` has a `primary_os_id` that links to an `os_id` on the `os` table, then two possible generated methods could exist: one for the table name `getOs_Object` and one based on the field `getPrimaryOs_Object`. `getPrimaryOs_Object` is preferred.
		
		Generally speaking, the table name will be used to generate the _Object accessor names (required for multi-key FK), but when there is only one FK the field name might be used (with the `_id`, of course).
	
	
2. Be easy to understand and use.
	* No `checkOnceAndGet` or `coag` methods


