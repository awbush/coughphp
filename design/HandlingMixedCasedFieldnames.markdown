Handling Mixed Cased Fieldnames
===============================

If the generator sees a field name that contains mixed casing, then the generated function names for that field might be exactly that of the field name.

For example, if the field name is `TablePrefix_SampleField` then the generated getter (with no change) would be `getTablePrefixSampleField`. This might be confusing as it is now impossible to go backwards from the getter function name to the field name as that would incorrectly give `table_prefix_sample_field` as the field name.

We might want to change the generator (or provide an option) to allow the getter to be generated exactly as the field name; in this example, that would be `getTablePrefix_SampleField`.

However, this can cause collisions in the case of a field name like `SomeField_Collection`.

But, the examples I've seen of field names like this would benefit from several things being done to get from the field name to a sensible getter name:

	* Strip a prefix, e.g. `TablePrefix_` in this example.
	* Camel case (avoids the nameing collision, but may still lead to incompatible field_name <-> FieldName conversions).

For example, a table with the field names:

	Product_Product_ID
	Product_ProductName

would have getters:

	getProductID() <- notice the underscore is removed, the "I" is upper cased, but no effect since it was already upper cased, and the "D" was left alone.
	getProductName()

