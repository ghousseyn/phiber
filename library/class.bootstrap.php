<?php
/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */
class bootstrap extends main {
	protected $modules = array();
	protected $plugins = null;
	
	function __construct(){
		$this->getModules();
		$this->getPlugins();
	}
	static function getInstance(){
		return new self();
	}
	function getModules(){
	
		foreach (new DirectoryIterator(__dir__.'/modules') as $mods) {
    			if($mods->isDot()){
				continue;
			}
			
			if(is_dir(__dir__."/modules/".$mods->getFilename())){
				$dir = $mods->getFilename();
				$settings = __dir__."/modules/".$dir."/settings.xml";
				
				if(file_exists($settings)){
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
	function getPlugins(){
		$this->plugins = array();
		$path = __dir__.'/plugins';
		foreach (new DirectoryIterator($path) as $plugin) {
			if($plugin->isDot()){
				continue;
			}
	
			$dir = $path."/".$plugin->getFilename();
			if(is_dir($dir)){
	
				$this->plugins[] = $plugin->getFilename();
			}
	
		}
		
	}

	function getmods(){
		return $this->modules;
	}
}
?>
