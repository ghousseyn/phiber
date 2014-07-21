<?php

/**
 * Configuration class.
 * @version 	1.0
 * @author 	    Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */
class bootstrap
{

  protected $modules = array();

  protected $plugins = null;

  protected $config = null;

  public function __construct(config $config)
  {
    $this->config = $config;
  }


  public static function getInstance(config $config)
  {
    return new self($config);
  }

  public function getModules()
  {

    $path = $this->config->application. '/modules';
    $modules = $path.DIRECTORY_SEPARATOR.'modules.php';
    if(stream_resolve_include_path($modules)){
      $modList = include $modules;
    }else{
      $modList['g5475ff2f44a9102d8b7'] = 'phiber';
    }
    if($this->config->PHIBER_MODE === 'production' && stream_resolve_include_path($modules) && is_array($modList)){
      $this->modules = $modList;
      return $this;
    }
    // Auto discovery
    if(!is_dir($path)){
      $this->modules = array();
      return $this;
    }
    foreach(new DirectoryIterator($path) as $mods){
      if($mods->isDot()){
        continue;
      }

      if(is_dir($this->config->application. "/modules/" . $mods->getFilename())){

        $dir = $mods->getFilename();

        $this->modules[$dir] = $dir;

      }
    }

    if(count(array_diff($modList,$this->modules)) !== 0 || count(array_diff($this->modules,$modList)) !== 0 || isset($modList['g5475ff2f44a9102d8b7'])){
      $code = '<?php return '.tools::transcribe($this->modules).'; ?>';
      file_put_contents($modules, $code);
    }
    return $this;

  }

  public function isModule($mod)
  {
    if(isset($this->modules[$mod])){
      return true;
    }
    return false;
  }

  public function getPlugins()
  {
    //$this->plugins = array("context");
    /*
     * //Auto discovery
     *
     */
    $path = $this->config->application . DIRECTORY_SEPARATOR.'plugins';
    $plugins = $path.DIRECTORY_SEPARATOR.'plugins.php';

    if(stream_resolve_include_path($plugins)){
      $pluginsList = include $plugins;
    }else{
      $pluginsList['g5475ff2f44a9102d8b7'] = 'phiber';
    }

    if($this->config->PHIBER_MODE === 'production' && stream_resolve_include_path($plugins) && is_array($pluginsList)){
      return $this->plugins = $pluginsList;
    }




      foreach (new DirectoryIterator($path) as $plugin) {
       if ($plugin->isDot()) {
         continue;
      }
      $dir = $path . "/" . $plugin->getFilename();
      if (is_dir($dir)) {
        $this->plugins[] = $plugin->getFilename();
      }
     }

     if(count(array_diff($pluginsList,$this->plugins)) !== 0 || count(array_diff($this->plugins,$pluginsList)) !== 0 || isset($pluginsList['g5475ff2f44a9102d8b7'])){
       $code = '<?php return '.tools::transcribe($this->plugins).'; ?>';
       file_put_contents($plugins, $code);
     }
    return $this->plugins;

  }

  public function getmods()
  {
    return $this->modules;
  }
}
?>
