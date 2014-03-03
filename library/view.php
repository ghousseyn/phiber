<?php

/**
 * The view class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */
class view extends Codup\main
{

  protected $vars = array();

  function showTime()
  {
    if(! $this->conf->debug){
      $this->view->debuginfo = "";
    }
    if($this->get('layoutEnabled')){
      $this->renderLayout();
    }else{
      if(file_exists($this->viePath)){
        include $this->viewPath;
      }
    }

  }

}

