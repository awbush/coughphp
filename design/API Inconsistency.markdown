
Is there a potential API inconsistency in Cough?

Consider the following generated methods:

	loadProduct_Object()
	getProduct_Object()
	setProduct_Object($product)

And these calls (which are implemented in the core CoughObject class):

	loadObject('product');          // calls the custom load method (public)
	setObject('product', $product); // does not call the custom set method (protected)
	getObject('product', $product); // does not call the custom get method (protected)

We could change it...

	setObject('product', $product); // call the custom set method (public)
	getObject('product', $product); // call the custom get method (public)
	rawSetObject('product', $product); // does not call the custom set method (protected)
	rawGetObject('product', $product); // does not call the custom get method (protected)

And, we may have to make some internal Cough functions call the raw getters/setters.
