<?php
/*
 * Thanks to Aaron Ficher
 * http://www.aaron-fisher.com/articles/web/php/object-collections-in-php/
 */
namespace cosql;

class collection
{
	protected $objects; // array
	protected $deletedObjects; // array
	protected $resetFlag;
	protected $numObjects;
	protected $iterateNum;
	public function __construct()
	{
		$this->resetIterator();
		$this->numObjects = 0;
		$this->objects = array();
		$this->deletedObjects = array();
	}
	public function add($obj)
	{
		$this->objects[] = $obj;
		$this->numObjects++;
	}
	public function next()
	{
		$num = ($this->currentObjIsLast()) ? 0 : $this->iterateNum + 1;
		$this->iterateNum = $num;
	}
	public function isOdd()
	{
		return $this->iterateNum%2==1;
	}
	public function isEven()
	{
		return $this->iterateNum%2==0;
	}
	/*
	 get an obj based on one of it's properties.
	i.e. a User obj with the property 'username' and a value of 'someUser'
	can be retrieved by Collection::getByProperty('username', 'someUser')
	-- assumes that the obj has a getter method
	with the same spelling as the property, i.e. getUsername()
	*/
	public function getByProperty($propertyName, $property)
	{
		//$methodName = "get".ucwords($propertyName);
		foreach ($this->objects as $key => $obj) {
			if ($obj->{$propertyName} == $property) {
				return $this->objects[$key];
			}
		}
		return false;
	}
	/*
	 alias for getByProperty()
	*/
	public function findByProperty($propertyName, $property)
	{
		return $this->getByProperty($propertyName, $property);
	}
	/*
	 get an objects number based on one of it's properties.
	i.e. a User obj with the property 'username' and a value of 'someUser'
	can be retrieved by Collection::getByProperty('username', 'someUser')
	-- assumes that the obj has a getter method
	with the same spelling as the property, i.e. getUsername()
	*/
	public function getObjNumByProperty($propertyName, $property)
	{
		$methodName = "get".ucwords($propertyName);
		foreach ($this->objects as $key => $obj) {
			if ($obj->{$methodName}() == $property) {
				return $key;
			}
		}
		return false;
	}
	/*
	 get the number of objects that have a property
	with a value matches the given value
	i.e. if there are objs with a property of 'verified' set to 1
	the number of these objects can be retrieved by:
	Collection::getNumObjectsWithProperty('verified', 1)
	-- assumes that the obj has a getter method
	with the same spelling as the property, i.e. getUsername()
	*/
	public function getNumObjectsWithProperty($propertyName, $value)
	{
		$methodName = "get".ucwords($propertyName);
		$count = 0;
		foreach ($this->objects as $key => $obj) {
			if ($obj->{$methodName}() == $value) {
				$count++;
			}
		}
		return $count;;
	}
	/*
	 remove an obj based on one of it's properties.
	i.e. a User obj with the property 'username' and a value of 'someUser'
	can be removed by Collection::removeByProperty('username', 'someUser')
	-- assumes that the obj has a getter method
	with the same spelling as the property, i.e. getUsername()
	*/
	public function removeByProperty($propertyName, $property)
	{
		//$methodName = "get".ucwords($propertyName);
		foreach ($this->objects as $key => $obj) {
			if ($obj->{$propertyName} == $property) {
				$this->deletedObjects[] = $this->objects[$key];
				unset($this->objects[$key]);
				// reindex array & subtract 1 from numObjects
				$this->objects = array_values($this->objects);
				$this->numObjects--;
				$this->iterateNum = ($this->iterateNum >= 0) ? $this->iterateNum - 1 : 0;
				return true;
			}
		}
		return false;
	}
	public function currentObjIsFirst()
	{
		return ($this->iterateNum == 0);
	}
	public function currentObjIsLast()
	{
		return (($this->numObjects-1) == $this->iterateNum);
	}
	public function getObjNum($num)
	{
		return (isset($this->objects[$num])) ? $this->objects[$num] : false;
	}
	public function getLast()
	{
		return $this->objects[$this->numObjects-1];
	}
	public function removeCurrent()
	{
		$this->deletedObjects[] = $this->objects[$this->iterateNum];
		unset($this->objects[$this->iterateNum]);
		// reindex array & subtract 1 from iterator
		$this->objects = array_values($this->objects);
		if ($this->iterateNum == 0) { // if deleting 1st object
			$this->resetFlag = true;
		} elseif ($this->iterateNum > 0) {
			$this->iterateNum--;
		} else {
			$this->iterateNum = 0;
		}
		$this->numObjects--;
	}
	public function removeLast()
	{
		$this->deletedObjects[] = $this->objects[$this->numObjects-1];
		unset($this->objects[$this->numObjects-1]);
		$this->objects = array_values($this->objects);
		// if iterate num is set to last object
		if ($this->iterateNum == $this->numObjects-1) {
			$this->resetIterator();
		}
		$this->numObjects--;
	}
	public function removeAll()
	{
		$this->deletedObjects = array_merge($this->deletedObjects, $this->objects);
		$this->objects = array();
		$this->numObjects = 0;
	}
	/*
	 sort the objects by the value of each objects property
	$type:
	r regular, ascending
	rr regular, descending'
	n numeric, ascending
	nr numeric, descending
	s string, ascending
	sr string, descending
	*/
	public function sortByProperty($propName, $type='r')
	{
		$tempArray = array();
		$newObjects = array();
		while ($obj = $this->iterate()) {
			$tempArray[] = call_user_func(array($obj, 'get'.ucwords($propName)));
		}
		switch($type)
		{
			case 'r':
				asort($tempArray);
				break;
			case 'rr':
				arsort($tempArray);
				break;
			case 'n':
				asort($tempArray, SORT_NUMERIC);
				break;
			case 'nr':
				arsort($tempArray, SORT_NUMERIC);
				break;
			case 's':
				asort($tempArray, SORT_STRING);
				break;
			case 'sr':
				arsort($tempArray, SORT_STRING);
				break;
			default:
				return false;
		}
		foreach ($tempArray as $key => $val) {
			$newObjects[] = $this->objects[$key];
		}
		$this->objects = $newObjects;
	}
	public function isEmpty()
	{
		return ($this->numObjects == 0);
	}
	public function getCurrent()
	{
		return $this->objects[$this->iterateNum];
	}
	public function getObject($offset)
	{
	    return $this->objects[$offset];
	}
	public function __clone() 
	{
		foreach ($this->objects as &$a) {
		    $a = clone $a;
		}
	}
	
	public function setCurrent($obj)
	{
		$this->objects[$this->iterateNum] = $obj;
	}
	public function getObjectByIterateNum($iterateNum)
	{
		return (
				isset($this->objects[$iterateNum])
				? $this->objects[$iterateNum]
				: false
		);
	}
	public function iterate()
	{
		if ($this->iterateNum < 0) {
			$this->iterateNum = 0;
		}
		if ($this->resetFlag) {
			$this->resetFlag = false;
		} else {
			$this->iterateNum++;
		}
		if ( $this->iterateNum == $this->numObjects
				|| !isset($this->objects[$this->iterateNum])
		) {
			$this->resetIterator();
			return false;
		}
		return $this->getCurrent();
	}
	public function resetIterator()
	{
		$this->iterateNum = 0;
		$this->resetFlag = true;
	}
	public function __toString()
	{
		$str = '';
		foreach ($this->objects as $obj) {
			$str .= '--------------------------<br />'.$obj.'<br />';
		}
		return $str;
	}
	#################### GETTERS
	public function getDeletedObjects()
		{
		return $this->deletedObjects;
}
public function getIterateNum()
{
return $this->iterateNum;
}
public function getNumObjects()
{
return $this->numObjects;
}
		#################### SETTERS
		public function setDeletedObjects($key, $val)
		{
		$this->deletedObjects[$key] = $val;
}
public function resetDeletedObjects()
{
$this->deletedObjects = array();
}
}
?>