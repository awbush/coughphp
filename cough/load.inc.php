<?php
/**
 * Lightweight Collection-Handling Framework (CHF) for PHP5
 * "CoughPHP" for short. ;)
 * 
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
 * 
 * QUICK OVERVIEW:
 * 
 *     Cough is an extremely lightweight ORM framework for dealing with objects
 *     that have single table counterparts in a database.
 *
 *     Objects and Classes that extend the Cough abstract class have the
 *     following properties and capabilities:
 * 
 * PROPERTIES:
 * 
 *     Database table/row properties:
 * 
 *         + A Cough subclass has exactly one corresponding table in the database.
 *             - The table should have a primary key column (single or multi-key).
 *             - The Cough subclass carries definitions of the table name, key 
 *               column name(s), and any other columns.
 * 
 *         + Cough objects have (or can have, or will have) exactly one corresponding
 *           row in that table
 *     
 * CAPABILITIES:
 * 
 *     Constructor capabilities:
 * 
 *         + A Cough object constructed with no parameters creates an empty object 
 *           awaiting db insertion or other operation.
 * 
 *         + A Cough object constructed with an associative array creates an
 *           object and populates the fields on the object using data from
 *           the array. But, use ObjectName::constructByFields($assocArray) so
 *           that the proper type of object can be constructed.
 * 
 *         + Static construct methods are available for creating objects from
 *           data that already exists in the database. See the docs for more.
 * 
 * 
 * @author Anthony Bush, Tom Warmbrodt, Lewis Zhang
 * @copyright 2005-2008 Anthony Bush, Tom Warmbrodt, Lewis Zhang. CoughPHP is open source protected by the FreeBSD License.
 * @package cough
 **/

// Load the Cough framework
require_once('CoughIterator.class.php');
require_once('CoughKeyValueIterator.class.php');
require_once('CoughCollection.class.php');
require_once('CoughObject.class.php');
require_once('CoughObjectStaticInterface.class.php');
require_once('CoughDatabaseInterface.class.php');
require_once('CoughDatabaseResultInterface.class.php');
require_once('CoughDatabaseFactory.class.php');

// Load the query dependencies
require_once(dirname(dirname(__FILE__)) . '/as_query/load.inc.php');

?>
