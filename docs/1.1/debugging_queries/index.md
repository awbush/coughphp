---
title: Debugging Queries - CoughPHP
---

Debugging Queries
=================

When using the default `as_database` abstraction layer (if you don't know, you are), debug queries by turning on query logging with `startLoggingQueries` and then dumping the output with `getQueryLog`.  This is done on the database object itself, not the Cough object.

For example:

	<?php
	$db = ConcreteCoughObject::getDb();
	$db->startLoggingQueries();
	$newObject = ConcreteCoughObject::constructByKey(12345);
	echo '<pre>';
	print_r($db->getQueryLog());
	echo '</pre>';
	?>

If you're using a dev/production environment, you could setup your dev environment to dump all queries always:

	<?php
	// ... app config -- sets a constant DEV to 1 if environment is dev, else sets it to 0.
	
	if (DEV) {
		$db = CoughDatabaseFactory::getDatabase('main'); // fill in your db connection alias here
		$db->startLoggingQueries();
	}
	
	// ... do everything else / process request
	
	if (DEV) {
		$db = CoughDatabaseFactory::getDatabase('main');
		$queryLog = $db->getQueryLog();
		echo '<pre>';
		echo 'Total Query Time: ' . $db->getQueryLogTime() . ' seconds for ' . count($queryLog) . " queries.\n";
		print_r($queryLog);
		echo '</pre>';
	}
	?>

