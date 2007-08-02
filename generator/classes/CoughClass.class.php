<?php

/**
 * CoughGenerator creates these, CoughWriter compares them to what's on disk
 * and chooses which ones to write to disk (and where).
 * 
 * @package CoughPHP
 * @author Anthony Bush
 **/
class CoughClass {
	protected $className = null;
	protected $contents = null;
	protected $isStarterClass = false;
	protected $isCollectionClass = false;
	
	public function __construct($className = null, $contents = null, $isStarterClass = false, $isCollectionClass = false) {
		$this->setClassName($className);
		$this->setContents($contents);
		$this->setIsStarterClass($isStarterClass);
		$this->setIsCollectionClass($isStarterClass);
	}

	public function setClassName($className) {
		$this->className = $className;
	}
	
	public function setContents($contents) {
		$this->contents = $contents;
	}
	
	public function setIsStarterClass($isStarterClass) {
		$this->isStarterClass = $isStarterClass;
	}
	
	public function setIsCollectionClass($isCollectionClass) {
		$this->isCollectionClass = $isCollectionClass;
	}
	
	public function getClassName() {
		return $this->className;
	}
	
	public function getContents() {
		return $this->contents;
	}
	
	public function isStarterClass() {
		return $this->isStarterClass;
	}
	
	public function isCollectionClass() {
		return $this->isCollectionClass;
	}
	
}

?>