<?php
class index extends main{
	
	static function getInstance(){
		return new self();
	}

	function index(){
		return "this is cool<br />";
	}

	function action(){
		
		$this->view->text = "test";
		$this->view->origin = __class__;
		$this->view->file = __file__;
	}
}
?>
