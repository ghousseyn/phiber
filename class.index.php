<?php
class index extends main{
	
	
	function init(){
		
	}	

	static function getInstance(){
		return new self();
	}

	function index(){
		$this->stack("default:index");
		$this->db->select(array('translation',array('*'),''));
		$this->view->text = __class__;
	}

	function action(){
		
		$this->template->text = "this is root".$this->_request('var')." <br />";
		$this->template->origin = __class__;
		$this->template->file = __file__;
	}
}
?>
