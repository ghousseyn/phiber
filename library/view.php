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
      if(file_exists($this->viewPath)){
        include $this->viewPath;
      }
    }

    unset($_SESSION['phiber'][$this->keyHashes['view']]);
  }
  public function getURL()
  {
    return '/'.$this->route['module'].'/'.$this->route['controller'].'/'.$this->route['action'];
  }

}

