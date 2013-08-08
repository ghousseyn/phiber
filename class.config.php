<?php
/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghoucine@gmail.com>
 * @package 	abook
 */
class config extends main{
	
	/*
	 * Enable/disable debug
	 */
	public $debug = false;
	
	/*
	 * Instance of this class
	 */
	private static $instance = null;
	
	/*
	 * Configuration properties
	 */
	protected $_dbhost = "127.0.0.1";
	protected $_dbpass = "hggiHmfv";
	protected $_dbuser = "root";
	protected $_dbname = "abook";
	
	
	protected function __construct(){
		
	}
	
	static function getInstance(){
		if(null === self::$instance){
			$instance = new config();
		}
		
		return $instance;
	}
	
	/*
	 * No need for a getter for each of the properties or the methods
	 */
	function __get($var){
		if(key_exists($var, get_class_vars(__CLASS__))){
			parent::stack(__class__." --> $var");
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
