<?php
class index extends main{
	
	
	function init(){
		
	}	

	static function getInstance(){
		return new index;
	}

	function index(){
		return "this is cool<br />";
	}

	function action(){
		
		$this->_view()->text = "this is root".$this->_request('var')." <br />";
		$this->_view()->origin = __class__;
		$this->_view()->file = __file__;
	}
}
?>