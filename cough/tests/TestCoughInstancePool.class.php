<?php
// TODO: Need to roll this into it's own Unit test.
return;

class Animal
{
	public $id;
	public $type;
	public $name;

	protected function __construct($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public static function factoryById($id)
	{
		if (CoughInstancePool::has('Animal', $id))
		{
			return CoughInstancePool::get('Animal', $id);
		}
		else
		{
			$newAnimal = new Animal($id);
			CoughInstancePool::add($newAnimal, $id);
			return $newAnimal;
		}
	}
}

echo "<pre>";

$animal = Animal::factoryById(20);
$animal->type = 'dog';
$animal->name = 'Bowser';
print_r($animal);

$animal2 = Animal::factoryById(20);
print_r($animal2);

CoughInstancePool::remove('Animal', 20);

$animal3 = Animal::factoryById(20);
print_r($animal3);

echo "</pre>";

?>