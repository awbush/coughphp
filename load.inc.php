<?php
/**
 * Lightweight Collection-Handling Framework (CHF) for PHP5 :: version 1.0
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
 *         + A Cough subclass has exactly one corresponding table in the database.
 *             - The table should have a primary key column (single or multi-key).
 *             - The Cough subclass carries definitions of the table name, key 
 *               column name(s), and any other columns.
 *         + Cough objects have (or can have, or will have) exactly one corresponding
 *           row in that table
 *     
 * CAPABILITIES:
 * 
 *     Constructor capabilities:
 *         + A Cough object constructed with an associative array can automatically
 *           resolve its key ID and other legal values.
 *         + A Cough object constructed with an ID automatically assumes that ID 
 *           is its key ID and tries to get the rest of its identity from the database.
 *         + A Cough object constructed with no parameters creates an empty object 
 *           awaiting db insertion or other operation.
 * 
 * 
 * @author Anthony Bush, Tom Warmbrodt, Lewis Zhang
 * @copyright 2005-2007 Anthony Bush, Tom Warmbrodt, Lewis Zhang. CoughPHP is open source protected by the FreeBSD License.
 * @package CoughPHP
 **/

// Load the Cough framework
require_once('cough/CoughIterator.class.php');
require_once('cough/CoughCollection.class.php');
require_once('cough/CoughObject.class.php');

// 2007-10-28/AWB: No longer load the CoughLoader by default in order to give
// choice to users on whether they want to use that, an Autoloader or explicit inclusion.
// require_once('cough/CoughLoader.class.php');

// Load the DAL Adapter classes
require_once('dalal/CoughDatabaseFactory.class.php');

?>