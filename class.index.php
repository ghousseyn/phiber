<?php
class index extends main{
	
	
	function init(){
		
	}	

	static function getInstance(){
		return new self();
	}

	function index(){
		return "this is cool<br />";
	}

	function action(){
		
		$this->template->text = "this is root".$this->_request('var')." <br />";
		$this->template->origin = __class__;
		$this->template->file = __file__;
	}
}
?>
