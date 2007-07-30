<?php

include(dirname(__FILE__) . '/config.php');

echo 'Hello' . "<br />\n";

// CoughPHP revision 8 -> 12 went from 606,272 -> 564,744.
// Next up: Move collection saving into the collection and change CoughObject to just call save().
	// We might need to provide accessors to the object's collection definition, or
	// Pass it in through the save() function, or
	// Pass it in when we do setCollector(), or
	// ?
// After that, make setCollector() on a CoughObject set the appropriate object instead of (or maybe in addition to) the collector.
	// setCollector should continue to work? y/n
	// getCollector should continue to work? y/n
	// getObjectName_Object() will now work, just as it did before but now without checking a fresh copy into memory; it will use the reference.
	

// Note that all these meseasurements where taken with memory_get_usage() (not true)
// 647,600
// 1. Just the class: 43,772 (static or not)
// 2. $a = new Foo(); 44,580
// 3. $b = new Foo(); 45,240 (add 660)
// 4. $c = new Foo(); 45,920 (add 680)
// 5. $d = new Foo(); 46,584 (add 664)
// 6. Convert to statis: 46,328 (get back 256, 64 per instance)

// Added seven more columns for total of ten.
// 4 static instances: 49,368
// 4 non-static: 49,624 (still diff of only 256)

echo number_format(memory_get_usage()) . "<br />\n";
echo number_format(memory_get_usage(true)) . "<br />\n";

?>