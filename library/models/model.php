<?php 
namespace models;

use cosql\cosql;

class model implements \ArrayAccess 
{
    
    
    public function offsetGet($offset) {
    	return $this->{$offset}();
    }
    
    public function offsetSet($offset, $value) {
    	$this->{$offset}($value);
    }
    public function offsetExists($offset){
    	return in_array($offset, get_class_vars(get_class(new static())));
    }
    public function offsetUnset($offset){
    	if($this->offsetExists($offset)){
    		$this->set($offset, null);
    	}
    }
    public static function getInstance(){
        $obj = new static();
        
        return self::getCoSQL(get_class($obj));;
    }
	public static function getCoSQL($class){
		//$class = __NAMESPACE__."\\".$class;
		$instance = new \cosql\basemodel($class);
		return $instance->getCoSQL();
	}
	public function save($obj){
	
	  $cosql = self::getCoSQL(get_class($obj));
	  $cosql->save($obj);
	
	}
	function __set($var, $val){
		$this->{$var}($val);
		
	}
	function __get ($var)
	{
		if (key_exists($var, get_class_vars(get_class(new static())))) {
			return $this->{$var}();
		}
	
	}
}
?>