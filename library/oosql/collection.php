<?php
/*
 * Thanks to Aaron Ficher
 * http://www.aaron-fisher.com/articles/web/php/object-collections-in-php/
 */
namespace Phiber\oosql;

class collection extends \ArrayObject
{
    public $obj_name = null;
    protected $objects; // array
    protected $deletedObjects; // array
    protected $resetFlag;
    protected $numObjects;

    public function __construct()
    {
        parent::setFlags(parent::ARRAY_AS_PROPS);

        $this->numObjects = 0;
        $this->objects = array();
        $this->deletedObjects = array();
    }

    public function add($obj)
    {
        $this->objects[] = $obj;
        $this->numObjects++;
    }

    public function addBulk($objects)
    {
        $this->objects = $objects + $this->objects;
        $this->numObjects = count($this->objects);
    }

    /*
     * get an obj based on one of it's properties. i.e. a User obj with the
     * property 'username' and a value of 'someUser' can be retrieved by
     * Collection::objectWhere('username', 'someUser')
     */
    public function objectWhere($property, $value)
    {
        foreach ($this->objects as $key => $obj) {
            if ($obj->{$property} === $value) {
                return $this->objects[$key];
            }
        }
        return false;
    }

    public function objectsWhere($property, $value)
    {
        $collection = new self();
        foreach ($this->objects as $key => $obj) {
            if ($obj->{$property} === $value) {
                $collection->add($obj);
            }
        }
        return $collection;
    }

    /*
     * alias for objectWhere()
     */
    public function findOne($property, $value)
    {
        return $this->objectWhere($property, $value);
    }

    /*
     * alias for objectsWhere()
     */
    public function findAll($property, $value)
    {
        return $this->objectsWhere($property, $value);
    }

    /*
     * get matched objects keys based on one of it's properties. i.e. a User obj key
     * with the property 'username' and a value of 'someUser' can be retrieved by
     * Collection::keyWhere('username', 'someUser')
     */
    public function keyWhere($property, $value)
    {
        foreach ($this->objects as $key => $obj) {
            if ($obj->{$property} === $value) {
                $keys[] = $key;
            }
        }
        if (count($keys)) {
            return $keys;
        }
        return false;
    }

    /*
     * get the number of objects that have a property with a value matches the
     * given value i.e. if there are objs with a property of 'verified' set to 1
     * the number of these objects can be retrieved by:
     * Collection::countWhere('verified', 1)
     */
    public function countWhere($property, $value)
    {

        $count = 0;
        foreach ($this->objects as $key => $obj) {
            if ($obj->{$property} === $value) {
                $count++;
            }
        }
        return $count;
    }

    /*
     * remove an obj based on one of it's properties. i.e. a User obj with the
     * property 'username' and a value of 'someUser' can be removed by
     * Collection::removeWhere('username', 'someUser')
     */
    public function removeWhere($property, $value)
    {
        $results = array();
        foreach ($this->objects as $key => $obj) {
            if ($obj->{$property} === $value) {
                $this->deletedObjects[] = $this->objects[$key];
                $results[] = $this->objects[$key];
                unset($this->objects[$key]);
                $this->numObjects--;
            }
        }
        $this->objects = array_values($this->objects);
        return $results;
    }

    public function restoreWhere($property, $value)
    {
        foreach ($this->deletedObjects as $key => $obj) {
            if ($obj->{$property} === $value) {
                $this->add($obj);
                unset($this->deletedObjects[$key]);
            }
        }
        $this->deletedObjects = array_values($this->deletedObjects);
    }

    public function getLast()
    {
        return $this->objects[$this->numObjects - 1];
    }

    public function pop()
    {
        if ($this->count() == 0) {
            return false;
        }
        $element = $this->objects[$this->numObjects - 1];
        $this->deletedObjects[] = $element;
        unset($this->objects[$this->numObjects - 1]);
        $this->objects = array_values($this->objects);
        $this->numObjects--;
        return $element;
    }

    public function deleteAll()
    {
        $this->deletedObjects = array_merge($this->deletedObjects, $this->objects);
        $this->objects = array();
        $this->numObjects = 0;
    }

    public function destroy()
    {
        $this->deleteAll();
        unset($this->deletedObjects);
    }

    /*
     * sort the objects by the value of each objects property $type: r regular,
     * ascending rr regular, descending' n numeric, ascending nr numeric,
     * descending s string, ascending sr string, descending
     */
    public function sortByProperty($property, $type = 'r')
    {
        $tempArray = array();
        $newObjects = array();
        foreach ($this->objects as $obj) {
            $tempArray[] = $obj->{$property};
        }
        switch ($type) {
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
        return ($this->numObjects === 0);
    }

    public function exists($object)
    {
        foreach ($this->objects as $obj) {
            if ($object === $obj) {
                return true;
            }
        }
        return false;
    }

    public function Object($offset = 0)
    {
        return $this->objects[$offset];
    }

    public function getDeleted()
    {
        return $this->deletedObjects;
    }

    public function resetDeleted()
    {
        $this->deletedObjects = array();
    }

    /*
     * Clone every object we have
     */
    public function __clone()
    {
        foreach ($this->objects as &$a) {
            $a = clone $a;
        }
    }

    public function __invoke($key)
    {
        return $this->objects[$key];
    }

    public function offsetGet($offset)
    {
        return $this->objects[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->objects[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return key_exists($offset, $this->objects);
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->objects[$offset]);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    public function toArray()
    {
        return $this->objects;
    }

    public function toJSON($options = null)
    {
        return json_encode($this->objects, $options);
    }

    public function count()
    {
        return $this->numObjects;
    }

    public function __call($func, $args)
    {
        return call_user_func_array(array($this->Object(), $func), $args);
    }
}

?>
