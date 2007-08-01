<?php

/**
 * There is no interface for the column driver at this time. Since the class
 * that implements DriverTable is the one that constructs the DriverColumn,
 * it needs only know how to interact with itself in order to set all
 * the column's attributes.
 *
 * @author Anthony Bush
 **/
interface DriverColumn {
}

?>