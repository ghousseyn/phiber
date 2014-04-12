<?php

/**
 * The view class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */
class view extends Phiber\phiber
{
  public function showTime()
  {

    if($this->get('layoutEnabled')){
      $this->renderLayout();
    }else{
      if(stream_resolve_include_path($this->viewPath)){
        include $this->viewPath;
      }
    }

    Phiber\Session\session::delete($this->keyHashes['view']);

  }
  public function getURI()
  {
    return '/'.$this->route['module'].'/'.$this->route['controller'].'/'.$this->route['action'];
  }

}

