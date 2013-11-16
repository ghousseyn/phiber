<?php
namespace models;
use Codup;
class translation extends model  
{
	private $link;
	
    public $id;
    public $tr_en;
    public $tr_fr;
    public $tr_ar;
    public $key;
    public $conValues = array();
    public $new = true;
    public $fromFlag = false;
    public $sql;
    
    public function __construct()
    {
    	$this->link = new \cosql\cosql();
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
    
    	
    	return $this;
    }
    
    public function from($table = __CLASS__)
    {
    	if(strstr($table,'\\')){
    		$table = str_replace('\\','',strstr($table,'\\'));
    	}
    	$this->sql .= " FROM $table";
    	$this->fromFlag = true;
    	return $this;
    }
    
    public function join($table,$criteria)
    {
    	if(!$this->fromFlag){
    		$this->from();
    	}
    	$this->sql .= " JOIN $table ON $criteria ";
    	return $this;
    }
    
    public function where($condition, $value)
    {
    	if(!$this->fromFlag){
    		$this->from();
    	}
    	$this->sql .= " WHERE $condition";
    	$this->conValues[] = $value;
    	return $this; 
    }
    
    public function exec()
    {
    	$this->stmt = $this->link->prepare($this->sql);
    	$values = "";
    	if(count($this->conValues)){
    		$values = $this->conValues;
    	}
    	$this->stmt->execute($values);
    	$this->stmt->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, __CLASS__);
    	return $this->stmt;
    }
    
    public function limit($from, $to){
    	$this->sql .= " LIMIT $from, $to";
    	return $this;
    }
    
    public function getPrimary() 
    {
        return $this->id;
    }
    
    public function getPrimaryByValue($value)
    {
    	return $this->findOne(array('id' => $value))->new(false);
    }
    
    public function getByValue($arg, $operator = null, $fields = array('*'))
    {
    	return $this->find($arg,$fields,$operator)->new(false);
    }
    
    public function findOne( $arg, $operator = null, $fields = array('*'))
    {
    	return $this->find($arg,$fields,$operator)->limit(0,1);
    }
    
    public function find( $arg, $operator = null, $fields = array('*'))
    {
    	if($operator == null){
    		$operator = '=';
    	}
    	$select = $this->select(implode(',',$fields));
    	foreach($arg as $col => $val){
    		$select->where("$col $operator ?",$val);
    	}
    	$this->stmt = $select->exec();
    	return $this;
    }
    
}
?>