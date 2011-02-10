<?php

// override "save" on Book class
class UnsaveableBook extends Book
{
	public function save()
	{
		return false;
	}
}
