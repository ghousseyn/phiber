<?php
/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */
class bootstrap extends main {
	protected $modules = array();

	function __construct(){
		$this->getModules();
	}
	static function getInstance(){
		return new bootstrap;
	}
	function getModules(){
		foreach (new DirectoryIterator('/home/hussein/Documents/www/dev/modules') as $mods) {
    			if($mods->isDot()){
				continue;
			}
			
			if(is_dir("./modules/".$mods->getFilename())){
				$dir = $mods->getFilename();
				$file = "./modules/".$dir."/defaults.xml";
				$this->modules[$mods->getFilename()]  = $file;
				if(file_exists($file)){
					
				}
				$this->modules[$dir] = $dir;
			}

		}
		
	}
	function isModule($mod){
		if(array_key_exists($mod, $this->modules)){
			return true;
		}
		return false;
	}
	function getmods(){
		return $this->modules;
	}
}
?>
