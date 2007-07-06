<?php

/**
 * undocumented class DynaCoughCollection
 **/
class DynaCoughCollection extends CoughCollection {
	/*
		TODO: a DynaCoughObject figures out its attributes all on its own, given a tablename
		IF you're getting a BUNCH of DynaCoughObjects and loading them into a CoughObject, 
		that is a big waste.
		
		The DynaCoughCollection would require that its subclasses override its own
		defineCore() method. This method would set the initial dbName, tableName, and
		'collecting statement' sql.
		
		It would then perform one-and-only-one schema seek (a la DynaCoughObject) and as it
		loaded itself with the results of the collecting sql, it would pass the schema to the
		DynaCoughObjects it instantiated. This would cause the DynaCoughObject to NOT look up
		its own data structure -- it would just take the stuff passed in.
		
		Word!
	*/
	protected $init_dbName;
	protected $init_tableName;
	protected $init_statement;
	
}


?>
