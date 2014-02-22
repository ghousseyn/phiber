<?php
namespace oosql;
require 'collection.php';

class cogen extends \PDO {
	
    protected $queries = array('tables'=>'SHOW TABLES',
            				   'columns'=>'SHOW COLUMNS FROM',
                               'create'=>'show create table');
    
    protected $except = array();
    protected $errors = array();
    protected $time;
    protected $mem;
    
    public $path = 'g:/models/';

    
    function __construct($host,$dbname,$user,$password){
        
        try {
        
        	parent::__construct('mysql:host='.$host.';dbname='.$dbname, $user, $password);
        	 
        } catch (\PDOException $e) {
        
        	$this->errors[] = $this->errorInfo();
        	die();
        }
        $this->time = microtime(true);
        $this->mem = memory_get_usage();
       
    }
    function getErrors(){
    	return $this->errors;
    }
    private function getCollection($query){
        
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
        foreach($tables as $tble){
    		$tbls[] = (array) $tble;
    	}
    	foreach($tbls as &$tbl){
    	 
    	    
    	    
    		$table = array_pop($tbl);
    		
    		print "Analyzing $table physical columns ...".PHP_EOL;
    		
    		$query = $this->queries['columns'].' '.$table.'';
    	 
    		$collection = $this->getCollection($query);
    		
    		if(!collection){
    		    
    			foreach($this->errors as $error){
    	    		print implode('|',$error).PHP_EOL;
    	    	}
    			return false;
    		}
    		foreach($collection as $columns) {
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
    		foreach($ddl as $ex){
    			$ks[]= (array) $ex;
    		}
    	 
    		$create = $ks[0]['Create Table'];
    
    		$keys = explode(',',$create);
    
    	
    		while(substr(trim($keys[count($keys)-1]),0,10) === 'CONSTRAINT' ){
    		 
    		 
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
        $cname = $tname;
        
        print "Generating class $cname ...";
        
    	$text .= '<?php'.PHP_EOL.'namespace models;'.PHP_EOL.'use Codup;';
    	$text .= PHP_EOL."class $cname extends model  ".PHP_EOL."{".PHP_EOL;
    	$count = 0;
    	$foreign = array();
    	$primary = array();
    	foreach($cols as $col){
    		//var_dump($fields);
    
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
    			$primary[] = $col['Field'];
    		}
    		
    		if($col['Field'] == ''){
    			continue;
    		}
    		$text .= '    public $'.$col['Field'].';'.PHP_EOL;
    		$count++;
    	}
    	$primaryCount = count($primary);
    	
    	if( $primaryCount !== 0){
    	
    		$text .= '    public function getPrimary() '.PHP_EOL.'	{'.PHP_EOL.'	    return array("'.implode('","', $primary).'");'.PHP_EOL.'	}'.PHP_EOL;
    		
    		if($primaryCount > 1){
    			$text .= '    public function getPrimaryValue($key=null)'.PHP_EOL.'	{'.PHP_EOL.'		if(null === $key){'.PHP_EOL.'			return $this->getCompositeValue();'.PHP_EOL.'		}'.PHP_EOL.'		$pri = $this->getPrimary();'.PHP_EOL.'		if(in_array($key,$pri)){'.PHP_EOL.'			return $this->{$pri[$key]};'.PHP_EOL.'		}'.PHP_EOL.'	}'.PHP_EOL;
    			$text .= '    public function getCompositeValue() '.PHP_EOL.'	{'.PHP_EOL.'		return array('.PHP_EOL;
    			foreach($primary as $pkey){
    				$text .= '				"'.$pkey.'" => $this->'.$pkey.','.PHP_EOL;
    			}
    			$text .= '				);'.PHP_EOL.'	}'.PHP_EOL;
    		}else{
    			$text .= '    public function getPrimaryValue($key=null)'.PHP_EOL.'	{'.PHP_EOL.'		if(null === $key){'.PHP_EOL.'			return array("'.implode("",$primary).'" => $this->'.implode("",$primary).');'.PHP_EOL.'		}'.PHP_EOL.'		$pri = $this->getPrimary();'.PHP_EOL.'		if(in_array($key,$pri)){'.PHP_EOL.'			return $this->{$pri[$key]};'.PHP_EOL.'		}'.PHP_EOL.'	}'.PHP_EOL;
    			$text .= '    public function getCompositeValue() '.PHP_EOL.'	{'.PHP_EOL.'		return false;'.PHP_EOL.'	}'.PHP_EOL;
    		}
    		//unset($primary);
    	}else{
    		$text .= '    public function getPrimary() '.PHP_EOL.'    {'.PHP_EOL.'        return false;'.PHP_EOL.'    }'.PHP_EOL;
    		$text .= '    public function getPrimaryValue() '.PHP_EOL.'    {'.PHP_EOL.'        return false;'.PHP_EOL.'    }'.PHP_EOL;
    		$text .= '    public function getCompositeValue() '.PHP_EOL.'	{'.PHP_EOL.'		return false;'.PHP_EOL.'	}'.PHP_EOL;
    	}
    
    	
    		$text .= '    public function getRelations() '
    		.PHP_EOL.
    		'    {'
    		.PHP_EOL.
    		'        return array(';
    		if(count($foreign)){ 
    			foreach ($foreign as $member => $content){
    				$text .= "'".$member."'=>'".$content[1].".".str_replace('`)','',$content[0])."',";
    			}
    		}
    		$text .= ');'
    		.PHP_EOL.
    		'    }'
    		.PHP_EOL;
    
    		 
    	
    	$text .= '    public function save() '
    	.PHP_EOL.
    	'    {'
    	.PHP_EOL.
    	'        parent::save($this);'
    	.PHP_EOL.
    	'    }'
    	.PHP_EOL;
    
    	$text .= '    public function load() '
    	.PHP_EOL.
    	'    {'
    	.PHP_EOL.
    	'        return parent::load($this);'
    	.PHP_EOL.
    	'    }'
    	.PHP_EOL;
    	$text .= '}'.PHP_EOL;
    	
    	$filename = $this->path.$cname.".php";
    
    	$f = fopen($filename, "w+");
    	$r = fwrite($f, $text);
    	fclose($f);
    	unset($text);
    	print " Done".PHP_EOL;
    	
    }
    print "Generated ".$h++." classes in ".number_format((microtime(true) - $this->time),4)." ms | Memory: ".number_format((memory_get_usage() - $this->mem)/1024,4)."kb";
    }
}