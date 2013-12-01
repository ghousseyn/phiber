<?php
namespace oosql;

use \Codup\main;

class oosql extends \PDO {
    
    public static $result;
    
    private $oosql_model_obj = null;
    private $oosql_limit = null;
    
    protected $oosql_class;
    protected $oosql_table;
    
    public $oosql_errors = array();
    public $oosql_stmt;
    public $oosql_conValues = array();
    public $oosql_numargs;
    public $oosql_fromFlag = false;
    public $oosql_multiFlag = false;
    public $oosql_del_multiFlag = false;
    public $oosql_del_numargs;
    public $oosql_sql;
    public $oosql_select;
    
    function __construct($oosql_table=null,$oosql_class=null){
        
      
        $this->oosql_class = $oosql_class;
        $this->oosql_table = $oosql_table;
        
        $conf = \config::getInstance();
        try {
        	 
        
        	 parent::__construct('mysql:host='.$conf->_dbhost.';dbname='.$conf->_dbname, $conf->_dbuser, $conf->_dbpass);
        	
        } catch (\PDOException $e) {
        	 
            $this->oosql_errors[] = $this->errorInfo();
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
    	$this->oosql_sql = "SELECT";
    	$numargs = func_num_args();
    
    	if ($numargs > 0) {
    		$arg_list = func_get_args();
    		for ($i = 0; $i < $numargs; $i++) {
    			if($i != 0 && $numargs > 1){
    				$this->oosql_sql .= ",";
    			}
    			$this->oosql_sql .= " ".$arg_list[$i];
    		}
    	}else{
    	    $this->oosql_sql .= " * ";
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
        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->oosql_sql .= ",";
        		}
        		$this->oosql_sql .= " ".$arg_list[$i];
        	}
        	
        }else{
            $this->oosql_sql .= " FROM $this->oosql_table ";
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
		   // var_dump((array)$this->oosql_model_obj);
		   $this->insert(implode(',',array_keys((array)$this->oosql_model_obj)))->values(implode(',',array_values((array)$this->oosql_model_obj)))->exec();
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
                $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9903,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
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
        if ($numargs > 0) {
        	$this->oosql_sql .= " VALUES (";
        	

        	for ($i = 0; $i < $numargs; $i++) {
        		if($i != 0 && $numargs > 1){
        			$this->oosql_sql .= ",";
        		}
        		$this->oosql_sql .= " ?";
        		
        	}
        	$this->oosql_conValues = $arg_list;
        	$this->oosql_sql .= ")";
        }
        
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
    	if($this->oosql_fromFlag){
    		$this->from();
    	}
    	$this->oosql_sql .= " $type JOIN $table ON $criteria ";
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
    	if($this->oosql_fromFlag){
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
    	
    	$this->oosql_sql .= " $clause $condition";
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
    public function exec()
    {
        if($this->oosql_fromFlag){
        	$this->from();
        }
        
        if(null != $this->oosql_limit){
            $this->oosql_sql = $this->oosql_sql." ".$this->oosql_limit;
        }
        
    	$this->oosql_stmt = $this->prepare($this->oosql_sql);
    	$values = "";
    	if(count($this->oosql_conValues)){
    		$values = $this->oosql_conValues;
    	}
    	$this->oosql_stmt->execute($values);
    	$this->oosql_stmt->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $this->oosql_class);
    	$f = fopen(__DIR__."\\log.txt","a+");
    	fputs($f, $this->oosql_sql.PHP_EOL);
    	fclose($f);
    	return $this->oosql_stmt;
    }
    public function fetch($fetch_mode=null)
    {     
       
        $this->oosql_stmt = $this->oosql_select->exec();
        $result = $this->oosql_stmt->fetchAll();
       
        $count = count($result);
        
        if($count == 0){
            $msg = "Query returned no results!";
            $this->oosql_errors[] = array('method'=> __METHOD__.':'.__LINE__,
                                    'message'=>$msg,
                                    'errno'=>9906,
                                    'query'=>$this->oosql_sql,
                                    'values'=>implode(',',$this->oosql_conValues)
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
    
    public function find( $arg, $operator = null, $fields = array('*'))
    {
    	if($operator == null){
    		$operator = '=';
    	}
    	$this->oosql_select = $this->select(implode(',',$fields));
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
    		$this->oosql_select->where("$col $operator ?",$val,$flag);
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
