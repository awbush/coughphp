<?php

require_once(dirname(dirname(__FILE__)) . '/dal/as_database/load.inc.php');
require_once('CoughDatabaseFactory.class.php');
require_once('drivers/base/CoughAbstractDatabaseAdapter.class.php');
require_once('drivers/base/CoughAbstractDatabaseResultAdapter.class.php');
require_once('drivers/as/CoughAsDatabaseAdapter.class.php');
require_once('drivers/as/CoughAsDatabaseResultAdapter.class.php');
require_once('drivers/pdo/CoughPdoDatabaseAdapter.class.php');
require_once('drivers/pdo/CoughPdoDatabaseResultAdapter.class.php');

?>