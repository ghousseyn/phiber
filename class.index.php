<<<<<<< HEAD
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
		
		$this->_view()->text = "this is root".$this->_request('var')." <br />";
		$this->_view()->origin = __class__;
		$this->_view()->file = __file__;
	}
}
?>
>>>>>>> refs/remotes/upstream/comvc
