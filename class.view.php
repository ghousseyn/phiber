<?php
class view extends main {

	protected $vars = array();

	static function getInstance(){
		
		return new self();
	}

	function showTime(){
		if($this->_layoutEnabled){
			$this->renderLayout();
		}else{

			include $this->viewPath;
		}
		
	} 
	
}
?>
