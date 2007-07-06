<?php

/**
 * undocumented class CoughIterator
 **/
class CoughIterator extends ArrayIterator implements Coughable {
	public function jam() {
		Cough::jam($this);
	}
}


?>
