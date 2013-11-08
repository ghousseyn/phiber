<?php
class view extends main {

	protected $vars = array();
	public static $instance = null;

	static function getInstance(){
		if(null == self::$instance){
			self::$instance = new view;
		}
		return self::$instance;
	}

	function showTime(){
		if($this->_layoutEnabled){
			$this->renderLayout();
		}else{

			include $this->viewPath;
		}
		
	} 

	function __set($var, $val){
		$this->vars[$var] = $val;
	}
	function __get($var){
		return $this->vars[$var];
	}
}
?>
