<?php

class CoughInstancePool
{
	protected static $instances = array();

	public static function add($instance, $id)
	{
		if (is_object($instance))
		{
			$className = get_class($instance);
			self::$instances[$className][$id] = $instance;
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function remove($instanceOrClassName, $id)
	{
		if (is_object($instanceOrClassName))
		{
			$className = get_class($object);
		}
		else
		{
			$className = $instanceOrClassName;
		}
		
		if (self::has($className, $id))
		{
			unset(self::$instances[$className][$id]);
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function get($className, $id)
	{
		if (self::has($className, $id))
		{
			return self::$instances[$className][$id];
		}
		else
		{
			return null;
		}
	}

	public static function has($className, $id)
	{
		if (isset(self::$instances[$className][$id]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function removeAll()
	{
		self::$instances = array();
		return true;
	}
}

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