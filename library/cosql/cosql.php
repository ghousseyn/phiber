<?php
namespace cosql;

use \Codup\main;

class cosql extends \PDO {
    
    public $errors = array();
    public static $result;
    
    private $cosql_model_obj = null;
    private $limit = null;
    
    protected $cosql_class;
    protected $cosql_table;
    
    public $cosql_stmt;
    public $cosql_conValues = array();
    public $cosql_numargs;
    public $cosql_fromFlag = false;
    public $cosql_multiFlag = false;
    public $cosql_del_multiFlag = false;
    public $cosql_del_numargs;
    public $cosql_sql;
    public $cosql_select;
    
    function __construct($cosql_table=null,$cosql_class=null){
        
      
        $this->cosql_class = $cosql_class;
        $this->cosql_table = $cosql_table;
        
        $conf = \config::getInstance();
        try {
        	 
        
        	 parent::__construct('mysql:host='.$conf->_dbhost.';dbname='.$conf->_dbname, $conf->_dbuser, $conf->_dbpass);
        	
        } catch (\PDOException $e) {
        	 
            $this->errors[] = $this->errorInfo();
        	die();
        }
 
    }
    public function create(){
        
    }
    public static function getInstance(){
        return new self();
    }
    
    public function select()
    {
    	$this->cosql_sql = "SELECT";
    	$numargs = func_num_args();
    
    	if ($numargs > 0) {
    		$arg_list = func_get_args();
    		for ($i = 0; $i < $numargs; $i++) {
    			if($i != 0 && $numargs > 1){
    				$this->cosql_sql .= ",";
    			}
    			$this->cosql_sql .= " ".$arg_list[$i];
    		}
    	}else{
    	    $this->cosql_sql .= " * ";
    	}
    
    	$this->cosql_fromFlag = true;
    	$this->cosql_select = $this;
    	return $this;
    }
    public function insert()
    {
        $this->cosql_sql = "INSERT INTO $this->cosql_table";
        
        $arg_list = func_get_args();
        $arg_list = explode(',',implode('',$arg_list));
        $numargs = count($arg_list);
        
        if ($numargs > 0) {
            $this->cosql_numargs = $numargs;
            $this->cosql_sql .= " ("; 
        
        	
        	$this->cosql_sql .= implode(",",$arg_list);
        	
        	
        	$this->cosql_sql .= ")";
        }
    	
    	return $this;
    }
    public function update(){
        
        $this->cosql_sql = "UPDATE";
        
        $numargs = func_num_args();
        
        if ($numargs > 0) {
            if($numargs > 1){
                $this->cosql_multiFlag = true;
            }
        	$arg_list = func_get_args();
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->cosql_sql .= ",";
        		}
        		$this->cosql_sql .= " ".$arg_list[$i];
        	}
        }else{
            $this->cosql_sql .= " $this->cosql_table";
        }
        
    	$this->cosql_sql .= " SET ";
        return $this;
    }
    public function delete(){
        
        $this->cosql_sql = "DELETE";
        
        $numargs = func_num_args();
        
        if ($numargs > 0) {
        	if($numargs > 1){
        		$this->cosql_del_multiFlag = true;
        		$this->cosql_del_numargs = $numargs;
        	}
        	$arg_list = func_get_args();
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->cosql_sql .= ",";
        		}
        		$this->cosql_sql .= " ".$arg_list[$i];
        	}
        	
        }else{
            $this->cosql_sql .= " FROM $this->cosql_table ";
        }
        
        
        return $this;
    }
    public function set($data){
        if(! is_array($data)){
            $msg = "Data should be passed as an array!";
            $this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9906,
                                    'query'=>$this->cosql_sql,
                                    'values'=>implode(',',$this->cosql_conValues)
                              ); 
            return false;
        }
        foreach($data as $field => $value){
            if($this->cosql_multiFlag){
                $this->cosql_sql .= " $field = $value,";
            }else{
                $this->cosql_sql .= " $field = ?,";
                $this->cosql_conValues[] = $value;
            }
            
        }
        $this->cosql_sql = rtrim($this->cosql_sql,',');
        return $this;
    }
    public function save($object=null){
		if(null == $object){
		    if(null == $this->cosql_model_obj){
		    	$msg = "Nothing to save!";
                $this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9902,
                                    'query'=>$this->cosql_sql,
                                    'values'=>implode(',',$this->cosql_conValues)
                              ); 
		    	return false;
		    }
		   // var_dump((array)$this->cosql_model_obj);
		   $this->insert(implode(',',array_keys((array)$this->cosql_model_obj)))->values(implode(',',array_values((array)$this->cosql_model_obj)))->exec();
		}else{
            
            $primary = $object->getPrimaryValue();

            while($t = self::$result->iterate()){
                if($t->getPrimaryValue() == $primary){
	                $old = $t;
                }
            }
            if(count((array)$old) >= count((array)$object)){
               
                foreach(array_diff((array)$object,(array)$old) as $key => $value){
                
                    $data[$key] = $value;
                }
            }else{
              
                foreach($object as $k => $v){
                    if($object->getPrimary() == $k){
                        continue;
                    }
                    $data[$k] = $v;
                }
            }
           
            if(null == $data){
                $msg = "Nothing to save!";
                $this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9903,
                                    'query'=>$this->cosql_sql,
                                    'values'=>implode(',',$this->cosql_conValues)
                              );
                return false;
            }
            $this->update()->set($data)->where($object->getPrimary().' = ?',$primary)->exec();
            
        }
       // if(isset($object->{$object->getPrimary()})){
            
        //}
        
        
     
    	return ;
    }
   
    public function values(){
        
        $arg_list = func_get_args();
        $arg_list = explode(',',implode('',$arg_list));
        $numargs = count($arg_list);
        
        if(($this->cosql_numargs != 0 && $numargs != $this->cosql_numargs) || $numargs == 0){
            $msg = "Columns and passed data do not match!";
            $this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9904,
                                    'query'=>$this->cosql_sql,
                                    'values'=>implode(',',$this->cosql_conValues)
                              );
            return false;
        }
        if ($numargs > 0) {
        	$this->cosql_sql .= " VALUES (";
        	

        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->cosql_sql .= ",";
        		}
        		$this->cosql_sql .= " ?";
        		
        	}
        	$this->cosql_conValues = $arg_list;
        	$this->cosql_sql .= ")";
        }
        
        $this->cosql_fromFlag = false;
        return $this;
    }
    public function from()
    {
    	if($this->cosql_del_multiFlag){
    	    
    	    $numargs = func_num_args();
    	    if($numargs < $this->cosql_del_numargs){
    	        $msg = "Columns and passed data do not match!";
    	        $this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9905,
                                    'query'=>$this->cosql_sql,
                                    'values'=>implode(',',$this->cosql_conValues)
                              );
    	        return false;
    	    }
    	    if ($numargs > 0) {
    	    	if($numargs > 1){
    	    		$this->cosql_del_multiFlag = true;
    	    	}
    	    	$arg_list = func_get_args();
    	    	for ($i = 0; $i < $numargs; $i++) {
    	    		if($i != 0 && $numargs > 1){
    	    			$this->cosql_sql .= ",";
    	    		}
    	    		$this->cosql_sql .= " ".$arg_list[$i];
    	    	}
    	    	 
    	    }
    	}
    	$this->cosql_sql .= " FROM $this->cosql_table";
    	$this->cosql_fromFlag = false;
    	return $this;
    }
    
    public function join($table,$criteria,$type="")
    {
    	if($this->cosql_fromFlag){
    		$this->from();
    	}
    	$this->cosql_sql .= " $type JOIN $table ON $criteria ";
    	return $this;
    }
    public function joinLeft($table,$criteria)
    {
        return $this->join($table, $criteria,$type="LEFT");
    }
    public function joinRight($table,$criteria)
    {
    	return $this->join($table, $criteria,$type="RIGHT");
    }
    public function where($condition, $value, $type=null)
    {
    	if($this->cosql_fromFlag){
    		$this->from();
    	}
    	switch($type){
    		case null:
    	        $clause = 'WHERE';
    	        break;
    		case 'or':
    		    $clause = 'OR';
    		    break;
    		case 'and':
    		    $clause = 'AND';
    		    break;
    		default:
    		    $clause = 'WHERE';
    	}
    	
    	$this->cosql_sql .= " $clause $condition";
    	$this->cosql_conValues[] = $value;
  
    	return $this;
    }
    public function andWhere($condition, $value)
    {
    	return $this->where($condition, $value, 'and');
    }
    public function orWhere($condition, $value)
    {
    	return $this->where($condition, $value, 'or');;
    }
    public function exec()
    {
        if($this->cosql_fromFlag){
        	$this->from();
        }
        
        if(null != $this->limit){
            $this->cosql_sql = $this->cosql_sql." ".$this->limit;
        }
        
    	$this->cosql_stmt = $this->prepare($this->cosql_sql);
    	$values = "";
    	if(count($this->cosql_conValues)){
    		$values = $this->cosql_conValues;
    	}
    	$this->cosql_stmt->execute($values);
    	$this->cosql_stmt->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $this->cosql_class);
    	$f = fopen(__DIR__."\\log.txt","a+");
    	fputs($f, $this->cosql_sql.PHP_EOL);
    	fclose($f);
    	return $this->cosql_stmt;
    }
    public function fetch($fetch_mode=null)
    {     
       
        $this->cosql_stmt = $this->cosql_select->exec();
        $result = $this->cosql_stmt->fetchAll();
       
        $count = count($result);
        
        if($count == 0){
            $msg = "Query returned no results!";
            $this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9906,
                                    'query'=>$this->cosql_sql,
                                    'values'=>implode(',',$this->cosql_conValues)
                              );
            return false;
        }
                
        $collection = new collection();
        for($i=0;$i < $count;$i++){
            $collection->add($result[$i]);
        }
        self::$result = clone $collection;
        return $collection;
    }
    
    public function limit($from, $to){
    	$this->limit = sprintf(" LIMIT %d, %d", $from, $to);
    	return $this;
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
    	$this->cosql_select = $this->select(implode(',',$fields));
    	if(!is_array($arg)){
    	    $obj = new $this->cosql_class;
    	    $arg = array($obj->getPrimary()=>$arg);
    	}
    	$i = 0;
    	foreach($arg as $col => $val){
    	    if($i>0){
    	        $flag = 'and';
    	    }else{
    	        $flag = "";
    	    }
    		$this->cosql_select->where("$col $operator ?",$val,$flag);
    		$i++;
    	}
    	
    	return $this;
    }
    function __set($var, $val){
        if(null != $this->cosql_model_obj){
            $this->cosql_model_obj->{$var} = $val;
        }else{
            $this->cosql_model_obj = new $this->cosql_class;
            $this->cosql_model_obj->{$var} = $val;
        }
    	return $this->cosql_model_obj;
    }
    
    
}
