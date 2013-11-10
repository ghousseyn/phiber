<<<<<<< HEAD
<?php
class apc extends main {

	function init(){

	}
	static function getInstance(){
		return new apc;
	}
	function act(){
		if ($quote = apc_fetch('star')) {
  			 $quote .= " [cached]";
  			
		} else { 
			$quote = "Do, or do not. There is no try. -- Yoda, Star Wars";  
	  		
	 		 apc_add('star', $quote, 120);
		}
		$this->_view()->dbg = $this->get('_request');
		$this->_view()->var = $this->_request("var");
	}
}
=======
<?php
class apc extends main {

	function init(){

	}
	static function getInstance(){
		return new apc;
	}
	function act(){
		if ($quote = apc_fetch('star')) {
  			 $quote .= " [cached]";
  			
		} else { 
			$quote = "Do, or do not. There is no try. -- Yoda, Star Wars";  
	  		
	 		 apc_add('star', $quote, 120);
		}
		$this->_view()->dbg = $this->get('_request');
		$this->_view()->var = $this->_request("var");
	}
}
>>>>>>> refs/remotes/upstream/comvc
