<?php
namespace cosql;

use \Codup\main;

class cosql extends \PDO {
    
    protected $link = null;
    protected $class;
    protected $table;
    
    public $stmt;
    public $conValues = array();
    public $numargs;
    public $fromFlag = false;
    public $multiFlag = false;
    public $sql;
    public $select;
    
    function __construct($table,$class){
        
      
        $this->class = $class;
        $this->table = $table;;
        $conf = \Codup\config::getInstance();
        try {
        	 
        
        	$this->link = parent::__construct('mysql:host='.$conf->_dbhost.';dbname='.$conf->_dbname, $conf->_dbuser, $conf->_dbpass);
        	
        } catch (\PDOException $e) {
        	print "Erreur !: " . $e->getMessage() . "<br/>";
        	die();
        }
        return $this->link;
    }
    public function link(){
        
    }
    public static function getInstance(){
        return new self();
    }
    
    public function select()
    {
    	$this->sql = "SELECT";
    	$numargs = func_num_args();
    
    	if ($numargs > 0) {
    		$arg_list = func_get_args();
    		for ($i = 0; $i < $numargs; $i++) {
    			if($i != 0 && $numargs > 1){
    				$this->sql .= ",";
    			}
    			$this->sql .= " ".$arg_list[$i];
    		}
    	}
    
    	$this->fromFlag = true;
    	return $this;
    }
    public function insert()
    {
        $this->sql = "INSERT INTO $this->table";
        
        $numargs = func_num_args();
        
        if ($numargs > 0) {
            $this->numargs = $numargs;
            $this->sql .= " ("; 
        	$arg_list = func_get_args();
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->sql .= ",";
        		}
        		$this->sql .= " ".$arg_list[$i];
        	}
        	$this->sql .= ")";
        }
    	
    	return $this;
    }
    public function update(){
        
        $this->sql = "UPDATE";
        
        $numargs = func_num_args();
        
        if ($numargs > 0) {
            if($numargs > 1){
                $this->multiFlag = true;
            }
        	$arg_list = func_get_args();
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->sql .= ",";
        		}
        		$this->sql .= " ".$arg_list[$i];
        	}
        }else{
            $this->sql .= " $this->table";
        }
        
    	$this->sql .= " SET ";
        return $this;
    }
    public function set($data){
        if(! is_array($data)){
            return false;
        }
        foreach($data as $field => $value){
            if($this->multiFlag){
                $this->sql .= " $field = $value,";
            }else{
                $this->sql .= " $field = ?,";
                $this->conValues[] = $value;
            }
            
        }
        $this->sql = rtrim($this->sql,',');
        return $this;
    }
    public function save($object){

        if(null != $object->getPrimaryValue()){
            
            $primary = $object[$object->getPrimary()];
var_dump($object);
            foreach($object as $key => $value){
                if($key == $object->getPrimary() || $key == 'changed'){
                    continue;
                }
                $data[$key] = $value;
            }
            $this->update()->set($data)->where($object->getPrimary().' = ?',$primary);//->exec();
            
            
         	$f = fopen(__DIR__."\\log.txt","w+");
         	fputs($f, $this->sql);
         	fclose($f);
        }
       // if(isset($object->{$object->getPrimary()})){
            
        //}
    	
    	return ;
    }
   
    public function values(){
        
        $numargs = func_num_args();
        
        if(($this->numargs != 0 && $numargs != $this->numargs) || $numargs == 0){
            return false;
        }
        if ($numargs > 0) {
        	$this->sql .= " VALUES (";
        	$arg_list = func_get_args();
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->sql .= ",";
        		}
        		$this->sql .= " '".$arg_list[$i]."'";
        	}
        	$this->sql .= ")";
        }
        $this->fromFlag = false;
        return $this;
    }
    public function from()
    {
    	
    	$this->sql .= " FROM $this->table";
    	$this->fromFlag = false;
    	return $this;
    }
    
    public function join($table,$criteria)
    {
    	if($this->fromFlag){
    		$this->from();
    	}
    	$this->sql .= " JOIN $table ON $criteria ";
    	return $this;
    }
    
    public function where($condition, $value)
    {
    	if($this->fromFlag){
    		$this->from();
    	}
    	if($this->multiFlag){
    	    $this->sql .= " WHERE $condition";
    	}else{
    	    $this->sql .= " WHERE $condition";
    	    $this->conValues[] = $value;
    	}
    	
    	return $this;
    }
    public function andWhere($condition, $value)
    {
    	if($this->fromFlag){
    		$this->from();
    	}
    	if($this->multiFlag){
    		$this->sql .= " AND $condition";
    	}else{
    		$this->sql .= " AND $condition";
    		$this->conValues[] = $value;
    	}
   
    	return $this;
    }
    public function orWhere($condition, $value)
    {
    	if($this->fromFlag){
    		$this->from();
    	}
    if($this->multiFlag){
    	    $this->sql .= " OR $condition";
    	}else{
    	    $this->sql .= " OR $condition";
    	    $this->conValues[] = $value;
    	}
    	return $this;
    }
    public function exec()
    {
    	$this->stmt = $this->prepare($this->sql);
    	$values = "";
    	if(count($this->conValues)){
    		$values = $this->conValues;
    	}
    	$this->stmt->execute($values);
    	$this->stmt->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $this->class);
    	return $this->stmt;
    }
    public function fetch($fetch_mode=null)
    {     
        $this->stmt = $this->select->exec();
        $result = $this->stmt->fetchAll();
        if(count($result) == 1){
            return $result[0];
        }
        
        $collection = new collection();
        for($i=0;$i <count($result);$i++){
            $collection->add($result[$i]);
        }
        return $collection;
    }
    
    public function limit($from, $to){
    	$this->sql .= sprintf(" LIMIT %d, %d", $from, $to);
    	return $this;
    }    
    public function getPrimaryByValue($value)
    {
    	return $this->findOne(array('id' => $value));
    }
    
    public function getByValue($arg, $operator = null, $fields = array('*'))
    {
    	return $this->find($arg,$fields,$operator);
    }
    
    public function findOne( $arg, $operator = null, $fields = array('*'))
    {
    	return $this->find($arg,$operator,$fields)->limit(0,1);
    }
    public function findLimited( $arg,$from, $to, $operator = null, $fields = array('*'))
    {
    	return $this->find($arg,$operator,$fields)->limit($from,$to);
    }
    
    public function find( $arg, $operator = null, $fields = array('*'))
    {
    	if($operator == null){
    		$operator = '=';
    	}
    	$this->select = $this->select(implode(',',$fields));
    	foreach($arg as $col => $val){
    		$this->select->where("$col $operator ?",$val);
    	}
    	
    	return $this;
    }
    
    function getCollection(){
     
           //	$this->dbh = new \PDO('mysql:host=localhost;dbname=fq', 'root', 'hggiHmfv');
        	

        $q = $this->link->query('SELECT * from dvd');
        $q->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'cosql\basemodel'); 
        $collection = new collection;
        while($res = $q->fetch()){
           $collection->add($res);
        }
        return $collection;
    }
}
