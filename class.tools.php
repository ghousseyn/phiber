<?php
class tools extends main {
	static function getInstance(){
		return new self();
	}
	function convertTime($size){
		$s = array('s','min','H');
		$e = floor(log($size)/log(60));
	 
		return sprintf('%d '.$s[$e], ($size/pow(60, floor($e))));
	}
}