<?php
class debug extends main {

	public $enabled = true;
	public $stack;

	private $timestart;
	protected $queries = array();
	protected static $dbg = null; 	

       	protected function __construct(){
		
	}
	
	static function getInstance(){
		if(null == self::$dbg){
			self::$dbg = new debug;
		}	
		return self::$dbg;
	}

	function start(){
		$this->timestart=microtime(true);
	}

	function execTime(){
		return number_format((microtime(true)-$this->timestart),4);
	}	

	function convert($size){
	    	$unit=array('b','kb','mb','gb','tb','pb');
    		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 	}

	function memoryUsage(){

		return $this->convert(memory_get_usage());
	}

	function stackPush($msg){
		array_unshift($_SESSION['stack'],$msg);
	}

	function stackTrace(){
		$str = "";
		if(isset($_SESSION['error']) && count($_SESSION['error'])){
			$str = "<br /> ================== Errors ================<br />";

			foreach($_SESSION['error'] as $k => $en){
			
			$str .= "[$k] : $en <br />";
		}
		}else{
		$str .= "<br /> ================== Steps =================<br />";
		foreach($_SESSION['stack'] as $key => $entry){
			
			$str .= "[$key] : $entry <br />";
		}
		}
		parent::stackFlush();
		return $str;
	}

	function __toString(){
		$str = "<br />-------------------------Debug output-------------------------<br />";
		$str .= "Stack: <br />".$this->stackTrace()."<br />";
		$str .= "Execution Time: ".$this->execTime()."<br />";
		$str .= "Memory Usage: ".$this->memoryUsage()."<br />";
		
		
		parent::errorStackFlush();
		return $str;
	}
}


?>
