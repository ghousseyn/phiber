<?php
namespace cosql;
require 'collection.php';

class cogen extends \PDO {
    protected $queries = array('tables'=>'SHOW TABLES',
            				   'columns'=>'SHOW COLUMNS FROM',
                               'create'=>'show create table');
    protected $path = './models/';
    protected $except = array();
    protected $errors = array();
    protected $time;
    protected $mem;
    
    function __construct($host,$dbname,$user,$password){
        
        try {
        
        	parent::__construct('mysql:host='.$host.';dbname='.$dbname, $user, $password);
        	 
        } catch (\PDOException $e) {
        
        	$this->errors[] = $this->errorInfo();
        	die();
        }
        $this->time = microtime();
        $this->mem = memory_get_usage();
       
    }
    function getCollection($query){
        
        $stmt = $this->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(self::FETCH_ASSOC);
        
        if(count($result) == 0){
        	$msg = "Query returned no results!";
        	$this->errors[] = array('method'=> __METHOD__.':'.__LINE__,
        							'message'=>$msg,
        							'errno'=>9906,
        							'query'=>$query,
        	        				'pdo'=>'PDO:'.$this->errorCode()
        							);
        	return false;
        }
        
        $collection = new collection();
        for($i=0;$i <count($result);$i++){
        	$collection->add($result[$i]);
        }
        
        return $collection;
    } 
    function generate(){
        
        print "Attempting tables discovery...".PHP_EOL;
        
    	$tables = $this->getCollection($this->queries['tables']);
    	if(!tables){
    	    foreach($this->errors as $error){
    	    	print implode('|',$error).PHP_EOL;
    	    }
    	    return false;
    	}
        while($table = $tables->iterate()){
    		$tbls[] = (array) $table;
    	}
    	foreach($tbls as $table){
    	 
    	    
    	    
    		$table = array_pop($table);
    		
    		print "Analyzing $table physical columns ...".PHP_EOL;
    		
    		$query = $this->queries['columns'].' '.$table.'';
    	 
    		$collection = $this->getCollection($query);
    		
    		if(!collection){
    		    
    			foreach($this->errors as $error){
    	    		print implode('|',$error).PHP_EOL;
    	    	}
    			return false;
    		}
    		while($columns = $collection->iterate()) {
    			$fields[$table][]= (array) $columns;
    		}
    		
    		print "Analyzing $table DDL ...".PHP_EOL;
    		
    		$ddl = $this->getCollection($this->queries['create'].' `'.$table.'`');
    		
    		if(!ddl){
    		
    			foreach($this->errors as $error){
    				print implode('|',$error).PHP_EOL;
    			}
    			return false;
    		}
    		
    		$ks = array();
    		while($ex = $ddl->iterate()) {
    			$ks[]= (array) $ex;
    		}
    	 
    		$create = $ks[0]['Create Table'];
    
    		$keys = explode(',',$create);
    
    	
    		while(substr(trim($keys[count($keys)-1]),0,10) == 'CONSTRAINT' ){
    		 
    		 
    			$key = array_pop($keys);
    			$parts = explode(' ',trim($key));
    			$constraint = trim($parts[1],'`');
    			$fkey = trim($parts[4],'(,),`');
    			$reftable = trim($parts[6],'`');
    			$reffield = trim($parts[7],'(,),`');
    			print "Found constraint $constraint ...".PHP_EOL;
    			$fields[$table][]['constraints'][$fkey] = array($reffield,$reftable);
    
    		}
    	}
    
    //$test = array_pop($fields);
    	
    foreach($fields as $tname => $cols){
        $h++;
        print "Generating class $tname ...";
    	$text .= '<?php'.PHP_EOL.'namespace models;'.PHP_EOL.'use Codup;';
    	$text .= PHP_EOL."class $tname extends model  ".PHP_EOL."{".PHP_EOL;
    	$count = 0;
    	$foreign = array();
    	foreach($cols as $col){
    		// var_dump($fields);
    
    		if(isset($col['constraints'])){
    			//print_r($fields[$tname]);
    			for($i =0;$i < count($fields[$tname]);$i++){
    				foreach($fields[$tname][$i] as $key => $val){
    					//print_r($col['constraints'][$val]);
    					if(isset($col['constraints'][$val])){
    						$foreign[$val] = $col['constraints'][$val];
    					}
    				}
    			}
    		}
    
    		if($col['Key'] == 'PRI'){
    			$primary = $col['Field'];
    		}
    		if($col['Field'] == ''){
    			continue;
    		}
    		$text .= '    public $'.$col['Field'].';'.PHP_EOL;
    		$count++;
    	}
    	if($primary != ""){
    		$text .= '    public function getPrimary() '.PHP_EOL.'    {'.PHP_EOL.'        return "'.$primary.'";'.PHP_EOL.'    }'.PHP_EOL;
    		$text .= '    public function getPrimaryValue() '.PHP_EOL.'    {'.PHP_EOL.'        return $this->'.$primary.';'.PHP_EOL.'    }'.PHP_EOL;
    		unset($primary);
    	}else{
    		$text .= '    public function getPrimary() '.PHP_EOL.'    {'.PHP_EOL.'        return false;'.PHP_EOL.'    }'.PHP_EOL;
    		$text .= '    public function getPrimaryValue() '.PHP_EOL.'    {'.PHP_EOL.'        return false;'.PHP_EOL.'    }'.PHP_EOL;
    	}
    
    	if(count($foreign)){
    		$text .= '    public function getRelations() '
    		.PHP_EOL.
    		'    {'
    		.PHP_EOL.
    		'        return array(';
    		 
    		foreach ($foreign as $member => $content){
    			$text .= "'".$member."'=>'".$content[1].".".str_replace('`)','',$content[0])."',";
    		}
    		$text .= ');'
    		.PHP_EOL.
    		'    }'
    		.PHP_EOL;
    
    		 
    	}
    	$text .= '}'.PHP_EOL;
    	$filename = $this->path.$tname.".php";
    
    	$f = fopen($filename, "w+");
    	$r = fwrite($f, $text);
    	fclose($f);
    	unset($text);
    	print " Done".PHP_EOL;
    	
    }
    print "Generated ".$h++." classes in ".(( microtime() - $this->time))." ms | Memory: ".((memory_get_usage() - $this->mem)/1024)."kb";
    }
}