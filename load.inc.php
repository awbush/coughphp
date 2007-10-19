<?php
/**
 * Lightweight Collection-Handling Framework (CHF) for PHP5 :: version 0.2
 * "Cough5" for short. ;)
 * Author: Tom Warmbrodt
 *
 * REQUIRES global_framework.php (which includes the definition Class Persistent and many other required bits of code)
 *       ___
 *      /' `\                                /'    | | |
 *     /'   ._)                             /'     | | |
 *    /'       ____              ____      /'__    | | |
 *   /'       /'    )--/'    /  /'    )   /'    )  | | |
 *  /'       /'    /' /'    /' /'    /'  /'    /'  | | |
 * (_____,/'(___,/'  (___,/(__(___,/(__/'    /(__  . . .
 *                               /'                
 *                       /     /'                  
 *                      (___,/'                    
 * QUICK OVERVIEW:
 * Cough is an extremely lightweight ORM framework for dealing with objects that have 
 * single table counterparts in a database.
 *
 * Objects and Classes that extend the Cough abstract class have the following properties and capabilities:
 * 
 * 	PROPERTIES:
 * 		Database table/row properties:
 * 			+ A Cough subclass has exactly one corresponding table in the database.
 * 				- The table should have a single primary key column
 * 				- The table can have a column that is considered the row's "name".
 * 				- The Cough subclass carries definitions of the table name, key 
 *				  column name, and "row name" column name.
 * 			+ Cough objects have (or can have, or will have) exactly one corresponding
 *			  row in that table
 * 		
 * 	CAPABILITIES:
 * 		Constructor capabilities:
 * 			+ A Cough object constructed with an associative array or CoughCollection
 *			  can automatically resolve its key id, name, and other legal values
 * 			+ A Cough object constructed with an ID automatically assumes that ID 
 *			  is its key ID and tries to get the rest of its identity from the database
 * 			+ A Cough object constructed with no parameters creates an empty object 
 *			  awaiting db insertion or other operation
 * 			
 *
 * @author Anthony Bush
 * @author Tom Warmbrodt
 * @author Wayne Wight
 * @author Lewis Zhang
 * @copyright 2005-2007 Tom Warmbrodt, Anthony Bush, Lewis Zhang. CoughPHP is open source protected by the FreeBSD License.
 * @package CoughPHP
 **/

// Load the Cough framework
require_once('cough/CoughIterator.class.php');
require_once('cough/CoughCollection.class.php');
require_once('cough/CoughObject.class.php');
require_once('cough/CoughLoader.class.php');

// Load the Database Abstraction Layer / Database Adapter classes (TODO: use the dalal stuff, for now I'm hard coding it to just keep using the As Database classes)
require_once('dal/as_database/load.inc.php');

?>