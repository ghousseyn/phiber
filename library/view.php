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

  protected $viewEnabled = true;

  protected $layoutEnabled = false;

  private $layout;

  public function __construct()
  {

      $this->layoutEnabled = $this->config->layoutEnabled;
      $this->layout = $this->config->application.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.$this->config->PHIBER_LAYOUT_FILE;
  }
  public function showTime()
  {

    if($this->layoutEnabled){
      $this->renderLayout();
    }else{
      if(stream_resolve_include_path($this->viewPath)){
        include $this->render();
      }
    }

  }
  protected function render()
  {
    $this->getView();
    if($this->viewEnabled){
      include $this->viewPath;
    }
  }
  protected function getView()
  {

    $path = array_slice($this->route, 0,3,true);

    $path = $this->config->application . '/modules/' . array_shift($path) . '/views/' . implode('/', $path) . '.php';

    $this->viewPath = $path;

  }
  public function disableView()
  {
    $this->viewEnabled = false;
  }
  public function disableLayout()
  {
    $this->layoutEnabled = false;
  }
  public function enableView()
  {
    $this->viewEnabled = true;
  }
  public function enableLayout()
  {
    $this->layoutEnabled = true;
  }
  public function setView($path)
  {
    if(stream_resolve_include_path($path)){
      $this->viewPath = $path;
    }
  }
  public function setLayout($path)
  {
    if(stream_resolve_include_path($path)){
      $this->layout = $path;
    }
  }
  protected function renderLayout()
  {
    include $this->layout;
  }
  public function getURI()
  {
    $uri = (isset($this->route['module'])?$this->route['module'].'/':'').
           (isset($this->route['controller'])?$this->route['controller'].'/':'').
           (isset($this->route['action'])?$this->route['action'].'/':'');
    return '/'.trim($this->phiber->getBase(),'/').'/'.$uri;
  }

  public static function getInstance()
  {
    if(null !== self::$instance){
      return self::$instance;
    }
    return self::$instance = new self;
  }
  public function __destruct()
  {
    $this->showtime();
  }
}
function T_($var)
{
  echo htmlentities($var);
}
