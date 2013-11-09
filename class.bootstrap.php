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
		return new self();
	}
	function getModules(){
		foreach (new DirectoryIterator(__dir__.'/modules') as $mods) {
    			if($mods->isDot()){
				continue;
			}
			
			if(is_dir("./modules/".$mods->getFilename())){
				$dir = $mods->getFilename();
				$settings = "./modules/".$dir."/settings.xml";
				
				if(file_exists($file)){
					$this->modules[$mods->getFilename()]['settings_path']  = $settings;
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
	
}
?>
