<?php
namespace oosql;

use \Codup\main;

class oosql extends \PDO {
    
    protected static $oosql_result = null;
    protected $oosql_class;
    protected $oosql_table;
    
    private $oosql_model_obj = null;
    private $oosql_limit = null;
    private $oosql_related = null;
    private $oosql_where = null;
    private $oosql_join = null;  
    private $oosql_errors = array();
    private $oosql_stmt;
    private $oosql_conValues = array();
    private $oosql_numargs;
    private $oosql_fromFlag = false;
    private $oosql_multiFlag = false;
    private $oosql_del_multiFlag = false;
    private $oosql_del_numargs;
    private $oosql_sql;
    private $oosql_select;
    
    function __construct($oosql_table=null,$oosql_class=null){
        
      
        $this->oosql_class = $oosql_class;
        $this->oosql_table = $oosql_table;
        
        $conf = \config::getInstance();
      
        try {
        	 
        	
        	 parent::__construct('mysql:host='.$conf->_dbhost.';dbname='.$conf->_dbname, $conf->_dbuser, $conf->_dbpass, array(\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_WARNING));
        	 $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
        	 
            $this->oosql_errors[] = $this->errorInfo();
           
        	die();
        }
        
    }
    
    public static function getPrevious(){
    	return self::$oosql_result;
    }
    public static function getInstance(){
        return new self();
    }
    public function getModelObject(){
    	if(null != $this->oosql_model_obj){
            return $this->oosql_model_obj;
        }else{
            return new $this->oosql_class;
            
        }
    }
    public function getErrors(){
    	return $this->oosql_errors;
    }
    public function select()
    {
    	$this->oosql_sql = "SELECT";
    	$numargs = func_num_args();
    
    	if ($numargs > 0) {
    		$arg_list = func_get_args();
    		for ($i = 0; $i < $numargs; $i++) {
    			if($i != 0 && $numargs > 1){
    				$this->oosql_sql .= ",";
    			}
    			$this->oosql_sql .= " $this->oosql_table.".$arg_list[$i];
    		}
    	}else{
    	    $this->oosql_sql .= " $this->oosql_table.* ";
    	}
    
    	$this->oosql_fromFlag = true;
    	$this->oosql_select = $this;
    	return $this;
    }
    public function insert()
    {
        $this->oosql_sql = "INSERT INTO $this->oosql_table";
        
        $arg_list = func_get_args();
        $arg_list = explode(',',implode('',$arg_list));
        $numargs = count($arg_list);
        
        if ($numargs > 0) {
            $this->oosql_numargs = $numargs;
            $this->oosql_sql .= " ("; 
        
        	
        	$this->oosql_sql .= implode(",",$arg_list);
        	
        	
        	$this->oosql_sql .= ")";
        }
    	
    	return $this;
    }
    public function update(){
        
        $this->oosql_sql = "UPDATE";
        
        $numargs = func_num_args();
        
        if ($numargs > 0) {
            if($numargs > 1){
                $this->oosql_multiFlag = true;
            }
        	$arg_list = func_get_args();
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->oosql_sql .= ",";
        		}
        		$this->oosql_sql .= " ".$arg_list[$i];
        	}
        }else{
            $this->oosql_sql .= " $this->oosql_table";
        }
        
    	$this->oosql_sql .= " SET ";
        return $this;
    }
    public function delete(){
        
        $this->oosql_sql = "DELETE";
        
        $numargs = func_num_args();
        
        if ($numargs > 0) {
        	if($numargs > 1){
        		$this->oosql_del_multiFlag = true;
        		$this->oosql_del_numargs = $numargs;
        	}
        	$arg_list = func_get_args();
        	if(is_array($arg_list[0])){
        		$this->oosql_fromFlag = true;
        		$this->where($arg_list[0][0], $arg_list[0][1])->exe();
        		return;
        	}
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->oosql_sql .= ",";
        		}
        		$this->oosql_sql .= " ".$arg_list[$i];
        	}
        	
        }else{
            $this->oosql_fromFlag = true;
        }
        
        
        return $this;
    }
    public function set($data){
        if(! is_array($data)){
            $msg = "Data should be passed as an array!";
            $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9906,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
                              ); 
            return false;
        }
        foreach($data as $field => $value){
            if($this->oosql_multiFlag){
                $this->oosql_sql .= " $field = $value,";
            }else{
                $this->oosql_sql .= " $field = ?,";
                $this->oosql_conValues[] = $value;
            }
            
        }
        $this->oosql_sql = rtrim($this->oosql_sql,',');
        return $this;
    }
    public function save($object=null){
		if(null == $object){
		    if(null == $this->oosql_model_obj){
		    	$msg = "Nothing to save!";
                $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9902,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
                              ); 
		    	return false;
		    }
		   // This is a brand new record let's insert;
		   $this->insert(implode(',',array_keys((array)$this->oosql_model_obj)))->values(implode(',',array_values((array)$this->oosql_model_obj)))->exe();
		}elseif(get_class(self::$oosql_result) == get_class($object)){
            
			//update the table after a select
			
            $primary = $object->getPrimaryValue();
            
            while($t = self::$oosql_result->iterate()){
                if($t->getPrimaryValue() == $primary){
	                $old = $t;
                }
            }
            if(count((array)$old) >= count((array)$object)){
               
                foreach(array_diff((array)$object,(array)$old) as $key => $value){
                
                    $data[$key] = $value;
                }
            }else{
            	
              $primary = $object->getPrimary();
                foreach($object as $k => $v){
                    if($primary == $k){
                        continue;
                    }
                    $data[$k] = $v;
                }
            }
           
            if(null == $data){
                $msg = "Nothing to save!";
                $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9903,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
                              );
                return false;
            }
            $this->update()->set($data)->where($object->getPrimary().' = ?',$primary)->exe();
            
        }else{
        	//update a related table (no select on it)
        	$primary = $object->getPrimary();
        	
        	foreach($object as $k => $v){
        		if($v === null || $primary == $k){
        			continue;
        		}	
        		$data[$k] = $v;
        	}
        	$this->update()->set($data)->where($object->getPrimary().' = ?',$object->getPrimaryValue())->exe();
        }
      
    	return true ;
    }
   
    public function values(){
        
        $arg_list = func_get_args();
        $arg_list = explode(',',implode('',$arg_list));
        $numargs = count($arg_list);
        
        if(($this->oosql_numargs != 0 && $numargs != $this->oosql_numargs) || $numargs == 0){
            $msg = "Columns and passed data do not match!";
            $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9904,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
                              );
            return false;
        }

        	$this->oosql_sql .= " VALUES (";
        	

        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->oosql_sql .= ",";
        		}
        		$this->oosql_sql .= " ?";
        		
        	}
        	$this->oosql_conValues = $arg_list;
        	$this->oosql_sql .= ")";
        
        
        $this->oosql_fromFlag = false;
        return $this;
    }
    public function from()
    {
    	if($this->oosql_del_multiFlag){
    	    
    	    $numargs = func_num_args();
    	    if($numargs < $this->oosql_del_numargs){
    	        $msg = "Columns and passed data do not match!";
    	        $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9905,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
                              );
    	        return false;
    	    }
    	    if ($numargs > 0) {
    	    	if($numargs > 1){
    	    		$this->oosql_del_multiFlag = true;
    	    	}
    	    	$arg_list = func_get_args();
    	    	for ($i = 0; $i < $numargs; $i++) {
    	    		if($i != 0 && $numargs > 1){
    	    			$this->oosql_sql .= ",";
    	    		}
    	    		$this->oosql_sql .= " ".$arg_list[$i];
    	    	}
    	    	 
    	    }
    	}
    	$this->oosql_sql .= " FROM $this->oosql_table";
    	$this->oosql_fromFlag = false;
    	return $this;
    }
    
    public function join($table,$criteria,$type="")
    {
    	
    	$this->oosql_join .= " $type JOIN $table ON $criteria ";
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
    	
    	$this->oosql_where .= " $clause $condition";
    	$this->oosql_conValues[] = $value;
  
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
    protected function valid_int($val){
    	if(is_int($val)) {
    		return true;
    	}
    	if(is_string($val)) {
    		return ctype_digit($val);
    	} 
    	return false;
    }
    public function exe()
    {
    	
    	
        if($this->oosql_fromFlag){
        	$this->from();
        }
        if(null != $this->oosql_join){
        	$this->oosql_sql .= $this->oosql_join;
        }
        if(null != $this->oosql_where){
        	$this->oosql_sql .= $this->oosql_where;
        }
        if(null != $this->oosql_limit){
            $this->oosql_sql = $this->oosql_sql." ".$this->oosql_limit;
        }
        
    	
    	if(count($this->oosql_conValues)){
    		$this->oosql_stmt = $this->prepare(trim($this->oosql_sql));
    		$ord = 1;
    		foreach($this->oosql_conValues as $val){
    			
    			if($this->valid_int($val)){

    				$this->oosql_stmt->bindValue($ord, $val, \PDO::PARAM_INT);
      				
    			}else{
    				
    				$this->oosql_stmt->bindValue($ord, $val, \PDO::PARAM_STR);
    			}	
    			$ord++;
    		}
    		$this->oosql_stmt->execute();
    		
    	}else{
    		$this->oosql_stmt = $this->query($this->oosql_sql);
    		
    	}
    	/*
    	$str = $this->oosql_sql." | ";
    	$str .= implode(', ',$this->oosql_conValues)."\r\n";
    	$f = fopen("g:\log.txt","a+");
    	fwrite($f, $str);
    	fclose($f);
    	*/
    	echo $this->oosql_sql."</br></br>";
    	$this->oosql_stmt->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $this->oosql_class);
    	
    	return $this->oosql_stmt;
    }
    public function fetch($fetch_mode=null)
    {     
       
        $this->oosql_select->exe();
        
        if (!$this->oosql_stmt) {
        	
            $msg = "Query returned no results!";
            $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>$this->errorInfo(),
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
                              );
            return false;
        }
        
        $result = $this->oosql_stmt->fetchAll();
        
        $collection = new collection();
        for($i=0;$i < count($result);$i++){
            $collection->add($result[$i]);
        }
        self::$oosql_result = clone $collection;
        return $collection;
    }
    public function with(array $related){
    	
    	$obj = new $this->oosql_class;
    	$relations = $obj->getRelations();
    	foreach ($relations as $fk => $target){
    		$table = substr($target,0,strpos($target,'.'));
    		if(in_array($table,$related)){
    			$this->oosql_sql .= " ,$table.*";
    			$this->join($table, "$this->oosql_table.$fk = $target");
    		}elseif(in_array($table,array_keys($related))){
    			foreach($related[$table] as $field){
    				$this->oosql_sql .= " ,$table.$field";
    			}
    			$this->join($table, "$this->oosql_table.$fk = $target");
    		}
    	}
    	return $this;
    }
    public function limit($from, $to){
    	$this->oosql_limit = sprintf(" LIMIT %d, %d", $from, $to);
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
    public function findAll()
    {
    	$this->oosql_select = $this->select('*');
    	return $this;
    }
    public function find( $arg, $operator = null, $fields = array('*'))
    {
    	if($operator == null){
    		$operator = '=';
    	}
    	if($fields[0] == '*'){
    		$object = new $this->oosql_class;
    		$fields = array_keys(get_class_vars($this->oosql_class));
    	}
    	$this->oosql_select = $this->select(implode(", $this->oosql_table.",$fields));
    	if(!is_array($arg)){
    	    $obj = new $this->oosql_class;
    	    $arg = array($obj->getPrimary()=>$arg);
    	}
    	$i = 0;
    	foreach($arg as $col => $val){
    	    if($i>0){
    	        $flag = 'and';
    	    }else{
    	        $flag = "";
    	    }
    		$this->oosql_select->where("$this->oosql_table.$col $operator ?",$val,$flag);
    		$i++;
    	}
    	
    	return $this;
    }
    function __set($var, $val){	
    	
        if(null != $this->oosql_model_obj){
            $this->oosql_model_obj->{$var} = $val;
        }else{
            $this->oosql_model_obj = new $this->oosql_class;
            $this->oosql_model_obj->{$var} = $val;
        }
    	return $this->oosql_model_obj;
    }
    
    
}
