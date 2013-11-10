<?php
class index extends main{
	

	static function getInstance(){
		return new self();
	}

	function index(){
		
	
	}

	function action(){
		$this->stack("default:".__method__);
		
	}
}
?>
