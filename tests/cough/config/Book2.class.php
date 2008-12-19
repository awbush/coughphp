<?php

// override "getTitle" on Book class
class Book2 extends Book
{
	public function getTitle()
	{
		return 'Your title is mine now!';
	}
}
