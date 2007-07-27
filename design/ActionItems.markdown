Action Items
============

* Finalize Cough interface.
* CoughPHP will be the channel (and website).
	* Update the website, de-register the #cough channel?

* Better docs on gets/sets.
	* should call your custom gets/sets. (BTW, these should be known as RAW getters/setters)
		* sounds like _checkObject (called by custom setter with SQL, etc.) vs checkObject (calls custom setter)
* Better docs on construction
	* By-passes your custom sets if you pass it data.


Definitions
-----------

	class SubCough extends CoughObject {
		
		protected $dbName = ;
	}

Construction
------------

	$order = new Order($id); // loads from database. Find out if load succeed via `$order->didCheckReturnResult()` <- TODO: Rename that function.

	$order = new Order($fields); // initializes the object with pre-existing data. `didCheckReturnResult` will return true, as it is assumed the pre-existing data was pulled from the source.

	$order->load(); // loads from the database using the current key id. This method is useful when trying to construct a multi-PK object
		// for example:
		$order = new Order($multiKeyHash);
		$order->load();

	$ticket = Ticket::construct($hash); // will switch through the hash to figure out with type of object to return, an Order or a Quote.
	
