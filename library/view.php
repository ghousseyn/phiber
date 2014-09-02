<?php
/**
 * View class.
 * @version 	1.0
 * @author 	Housseyn Guettaf <ghoucine@gmail.com>
 * @package 	Phiber
 */
namespace Phiber {

 class view extends phiber
 {
 /**
  * @var object \Phiber\view instance
  */
  private static $instance;
  /**
   * @var boolean
   */
  protected $viewEnabled = true;
  /**
   * @var boolean
   */
  protected $layoutEnabled = false;
  /**
   * Layout file path
   * @var string
   */
  private $layout;
  /**
   * Constructor get/set defaults
   * @return void
   */
  public function __construct()
  {

      $this->layoutEnabled = $this->config->layoutEnabled;
      $this->layout = $this->config->application.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.$this->config->PHIBER_LAYOUT_FILE;
  }
  /**
   * Outputs content (called in destructor)
   * @return void
   */
  public function showTime()
  {

    if($this->layoutEnabled){
      $this->renderLayout();
    }else{
      $this->render();
    }

  }
  /**
   * Called in the layout file to start render
   * @return void
   */
  protected function render()
  {

    if($this->viewEnabled){
      if(null == $this->view->viewPath){
        $this->getView();
      }
      include $this->view->viewPath;
    }
  }
  /**
   * Locate the current partial view using the current route
   * @return void
   */
  protected function getView()
  {

    $path = array_slice($this->phiber->route, 0,3,true);

    $path = $this->config->application . '/modules/' . array_shift($path) . '/views/' . implode('/', $path) . '.php';

    $this->view->viewPath = $path;

  }
  /**
   * Disables partial view of the current route
   * @return void
   */
  public function disableView()
  {
    $this->viewEnabled = false;
  }
  /**
   * Disables layout for the current request
   * @return void
   */
  public function disableLayout()
  {
    $this->layoutEnabled = false;
  }
  /**
   * Enables view
   * @return void
   */
  public function enableView()
  {
    $this->viewEnabled = true;
  }
  /**
   * Enables layout
   * @return void
   */
  public function enableLayout()
  {
    $this->layoutEnabled = true;
  }
  /**
   * Sets a different partial view
   * @param string $path
   * @return void
   */
  public function setView($path)
  {
    if(stream_resolve_include_path($path)){
      $this->viewPath = $path;
    }
  }
  /**
   * Sets a different layout
   * @param string $path
   * @return void
   */
  public function setLayout($path)
  {
    if(stream_resolve_include_path($path)){
      $this->layout = $path;
    }
  }
  /**
   * Includes layout file
   * @return void
   */
  protected function renderLayout()
  {
    include $this->layout;
  }
  /**
   * Returns the absolute URI of the current route
   * @return string
   */
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
    if($this->viewEnabled || $this->layoutEnabled){
      $this->showtime();
    }
  }
 }
}
namespace {
  /**
   * Escape and print
   * @param string $var
   */
 function T_($var)
 {
   echo htmlentities($var);
 }
}