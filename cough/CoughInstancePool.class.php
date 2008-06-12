<?php

/**
 * Unused class, but will be available in the future.
 *
 * @package cough
 **/
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
			$className = get_class($instanceOrClassName);
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

?>