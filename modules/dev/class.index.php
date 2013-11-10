<<<<<<< HEAD
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
=======
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
		
		$this->_view()->text = $_SERVER;
		$this->_view()->origin = __class__;
		$this->_view()->file = __file__;
	}
}
?>
>>>>>>> refs/remotes/upstream/comvc
