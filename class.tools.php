<?php
/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */

class tools extends main {
	static function getInstance(){
		return new self();
	}
	function convertTime($size){
		if(0 == $size){
			return;
		}
		$s = array('s','min','H');
		$e = floor(log($size)/log(60));
	 
		return sprintf('%d '.$s[$e], ($size/pow(60, floor($e))));
	}
}