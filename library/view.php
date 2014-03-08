<?php

/**
 * The view class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */
class view extends Phiber\main
{

  protected $vars = array();

  public function showTime()
  {
    if(! $this->conf->debug){
      $this->view->debuginfo = "";
    }
    if($this->get('layoutEnabled')){
      $this->renderLayout();
    }else{
      if(file_exists($this->viewPath)){
        include $this->viewPath;
      }
    }

  }
  public function getURL()
  {
    return '/'.$this->route['module'].'/'.$this->route['controller'].'/'.$this->route['action'];
  }

}

