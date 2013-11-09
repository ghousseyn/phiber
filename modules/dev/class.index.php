<?php
class index extends main{
	
	static function getInstance(){
		return new self();
	}

	function index(){
		return "this is cool<br />";
	}

	function action(){
		
		$this->template->text = $_REQUEST;
		$this->template->origin = __class__;
		$this->template->file = __file__;
	}
}
?>
