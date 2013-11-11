<?php
/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */

class config extends main{
	
	/*
	 * 
	 * Path to the library relative to this file (set in the construct)
	 */
	protected $library = null;
	/*
	 * Regenerate session id or not (false will disable the functionality)
	 * Set it to true for a default value of 1800 seconds (30 minutes)
	 * Or set it to whatever value you like to set in seconds
	 */
	protected $sessionReginerate = true; 
	/*
	 * If set to true the session will be destroyed after 1800 seconds (30 minutes) of inactivity
	 * Alternatively set the value that you like in seconds
	 * Set it to false to disable the functionality
	 */
	protected $inactive = true;
	/*
	 * Enable/disable debug
	 */
	public $debug = true;

	protected $inMemory = array('debug',
								'config',
								'tools',								
								); 
	
	/*
	 * DB configuration properties
	 */
	protected $_dbhost = "127.0.0.1";
	protected $_dbpass = "hggiHmfv";
	protected $_dbuser = "root";
	protected $_dbname = "fq";
	
	protected $layoutEnabled = true;
	
	protected function __construct(){
		$this->library = __dir__;
	}
	
	static function getInstance(){		
		return new self();
	}
	
	/*
	 * No need for a getter for each of the properties or the methods
	 */
	function __get($var){
		if(key_exists($var, get_class_vars(__CLASS__))){
		//	parent::stack(__class__." --> $var");
			return $this->{$var};
		}
		
	} 
	function __call($name, $param){
		if(array_search($name, get_class_methods(__CLASS__))){
			parent::stack(__class__." --> $name(".implode(',',$param).")");
			return call_user_func_array(array(__class__,$name),$param);
		}
		
	}

	static function __callStatic($name, $params){
		if(array_search($name, get_class_methods(__CLASS__))){
			parent::stack(__class__." --> $name(".implode(',',$param).")");
			return call_user_func_array(__class__."::".$name,$params);
		}
		
	}
	
}
?>
