
1. Should there be a method in CoughObject that returns a unique ID for objects (including ones with multi-column PK)?

	* Right now getKeyId() returns just the ID on single-column PK objects, but then it returns an array for multi-column PKs...
	
	* There is a need for a hash method.  How is CoughCollection supposed to maintain [pk] => [object] array if the PK is an array?

