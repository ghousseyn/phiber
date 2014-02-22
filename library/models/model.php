<?php 
namespace models;

use oosql\oosql;

abstract class model implements \ArrayAccess 
{

    protected static $oosql_model_extra = false;
    
    abstract public function getRelations();
    
    public function offsetGet($offset) {
    	return $this->{$offset};
    }
    
    public function offsetSet($offset, $value) {
    	$this->{$offset}= $value;
    }
    public function offsetExists($offset){
    	return key_exists($offset, get_class_vars(get_class(new static())));
    }
    public function offsetUnset($offset){
    	if($this->offsetExists($offset)){
    		$this->set($offset, null);
    	}
    }
    /*
     * Deliver an oosql instance instead and pass this class name so we can get back with the caller instance
     * with results
     */
    public static function getInstance(){
        
        return self::getooSQL(get_class(new static()));
    }
	protected static function getooSQL($class){
	    
	    $table  = strstr($class,'\\');
	    if($table){
	    	$table = trim(str_replace('\\','',$table));
	    }else{
	    	return false;
	    }
		 return new oosql($table,$class);
	}
	public function save($obj){
		
		if(self::$oosql_model_extra){
			
			$originalProps = get_class_vars(get_class(new static()));
			$mixedProps = array_keys(get_object_vars($obj));
		
			foreach($mixedProps as $property){
				if (!key_exists($property, $originalProps)){
					unset($obj->{$property});
				}
			}
			//self::$oosql_model_extra = false;
		}
	  
	   	$oosql = self::getooSQL(get_class($obj));
	  	$oosql->save($obj);
	
	}
	function load($obj){
		
		$primary = $obj->getPrimary();
		if(isset($obj->{$primary[0]})){
			return self::getooSQL(get_class($obj))->findOne($obj->getPrimaryValue())->fetch()->object();
		}
	}
	function __set($var, $val){
		if (!key_exists($var, get_class_vars(get_class(new static())))) {
			self::$oosql_model_extra = true;
		}
		$this->{$var}=$val;
	}
	public function __unset( $property ) {
		unset($this->properties[$property]);
	}
	function __call($table,$arg){
		$relations = $this->getRelations();
		$obj = $this;
		foreach ($relations as $fk => $target){
			$objPath = explode('.',$target);
			if($objPath[0] == $table){
				$table = "models\\$table";
				$instance = $table::getInstance()->getModelObject();
				$instance->{$objPath[1]} = $obj->{$objPath[1]};
				//var_dump($instance);
				return $instance;
			}
			
		}
	}
	function __get ($var)
	{
		if (key_exists($var, get_class_vars(get_class(new static())))) {
			return $this->{$var}();
		}
	
	}
}
?>