<?php

/**
 * The view class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */
class view extends Phiber\phiber
{
  private static $instance;

  public function showTime()
  {

    if($this->config->layoutEnabled){
      $this->renderLayout();
    }else{
      if(stream_resolve_include_path($this->viewPath)){
        include $this->render();
      }
    }

  }
  protected function render()
  {
    include $this->content;
  }
  public function getURI()
  {
    return '/'.$this->route['module'].'/'.$this->route['controller'].'/'.$this->route['action'];
  }


  public static function getInstance()
  {
    if(null !== self::$instance){
      return self::$instance;
    }
    return self::$instance = new self;
  }
}

