Sample code for how retrieval works now. Include examples on common problems (where you need to customize the queries to get extra data...)

	<?php
	$product = new Product(1);
	?>

	<?php
	$product = new Product();
	$product->checkBySql($sql);
	?>

	<?php
	$product = new Product();
	$product->checkByCriteria(array('product_id' => 1, 'product_status' => 0));
	?>

