<?php
namespace cosql;

class basemodel
{

    
    function getCollection($query,$class = null){
    	 try{
    		$this->dbh = new \PDO('mysql:host=localhost;dbname=edinar', 'root', 'hggiHmfv');
    	 }catch(Exception $e){
    	     die($e->getMessage());
    	 }
    if($class == null){
        $class = 'cosql\basemodel';
    }
    	$q = $this->dbh->query($query);
 
    	$q->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $class);
    	$collection = new collection;
    	while($res = $q->fetch()){
    		$collection->add($res);
    	}
    	return $collection;
    }
    
	function __set($var, $val){
		$this->{$var} = $val;
	}
	function __get ($var)
	{
		if (key_exists($var, get_class_vars(__CLASS__))) {
			return $this->{$var};
		}

	}
}