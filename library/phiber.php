<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Hussein Guettaf <ghussein@coda-dz.com>
 * @package     Phiber
 */
namespace Phiber;
require 'wire.php';
class phiber extends wire
{


  const PHIBER_ROUTE_FILE_PHP = 90;
  const PHIBER_ROUTE_FILE_YAML = 91;
  const PHIBER_ROUTE_FILE_XML = 92;

  private $path;
  private $uri;

  private $phiber_bootstrap;
  private $_requestVars = array();
  private $method;

  public function run($confFile = null)
  {
    spl_autoload_register(array($this, 'autoload'),true,true);

    if(null !== $confFile){
      $this->confFile = $confFile;

    }

    if(null == Session\session::get('phiber_flags')){
      Session\session::set('phiber_flags', 0);
    }
    $this->phiber_bootstrap = \bootstrap::getInstance($this->config);


    Session\session::start();

    Session\session::checkSession();

    if($this->config->PHIBER_LOG){

      error::initiate($this->logger());

    }

    $this->register('context', null);
    $this->register('layoutEnabled', $this->config->layoutEnabled);
    $this->register('ajax', false);

    $this->router(array('~/info/(\d+)/(\d+)~'=>'/flags'));

    $this->plugins();
    $this->dispatch();
    $this->getView();
    $this->view->showTime();

  }

  private function plugins()
  {

    foreach($this->phiber_bootstrap->getPlugins() as $plugin){
      $this->load($plugin, null, $this->config->application . '/plugins/' . $plugin . '/')->run();
    }
  }

  /**
   * @todo move path def to config
   */

  private function router(array $routes = null)
  {


    $this->uri = urldecode($_SERVER['REQUEST_URI']);

    if(null !== $routes && ($this->routeMatchSimple($routes, $this->uri) || $this->routeMatchRegex($routes, $this->uri))){

      return;
    }
    $this->method = $_SERVER['REQUEST_METHOD'];

    $this->register('http_method', $this->method);
    if($this->isValidURI($this->uri)){

      $parts = explode("/", trim($this->uri));
      array_shift($parts);
      if(! empty($parts[0]) && $this->phiber_bootstrap->getModules()->isModule($parts[0])){

        $module = array_shift($parts);
        $controller = $this->hasController($parts, $module);
        $action = $this->hasAction($parts, $controller);

      }else{

        $module = 'default';
        array_shift($parts);
        $controller = $this->hasController($parts, $module);
        $action = $this->hasAction($parts, $controller);

      }
      if(count($parts)){
        $this->setVars($parts);
      }

      if($this->isPost()){
        $this->_requestVars = $_POST;
      }

      $route = array('module' => $module, 'controller' => $controller, 'action' => $action, 'vars' => $this->get('_request'));
    }else{
      $route = array('module' => 'default', 'controller' => 'index', 'action' => \config::PHIBER_CONTROLLER_DEFAULT_METHOD);
      $this->path = $this->config->application . '/modules/default/';

    }

    if(! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
      $this->register('ajax', true);
    }
    $this->register('route', $route);
    $this->register('_request', $this->_requestVars);
  }

  private function routeMatchSimple($routes, $current)
  {
    if(isset($routes[$current])){

      if(is_array($routes[$current])){
        $this->route = $routes[$current];
      }elseif($this->isValidURI($routes[$current])){

        $_SERVER['REQUEST_URI'] = $routes[$current];

        $this->router();

      }
      return true;
    }
    return false;
  }
  private function routeMatchRegex($routes, $current)
  {

    foreach ($routes as $def => $route){
       if(preg_match_all($def, $current, $matches)){

         if(is_array($route)){
           $this->route = $route;
         }elseif($this->isValidURI($route)){
           $_SERVER['REQUEST_URI'] = $route;
           $this->router();
         }
         //\tools::wtf($matches);
         return true;
       }

    }
    return false;
  }
  private function isValidURI(&$uri)
  {
    $uri = str_replace('/?', '/', $uri);
    $uri = str_replace('?', '/?', $uri);
    $uri = str_replace('/?', '/', $uri);
    $uri = str_replace('&', '/', $uri);
    $uri = str_replace('=', '/', $uri);

    if(preg_match('~^(?:[/\\w\\s-\,\$\.\*\!\'\(\)\~]+)+/?$~u', $uri)){

      return true;
    }
    return false;
  }



  private function setVars($parts)
  {
    foreach($parts as $k => $val){
      if($k == 0 || ($k % 2) == 0){
        $value = (isset($parts[$k + 1]))?$parts[$k + 1]:null;
        $this->_requestVars[$parts[$k]] = $value;
      }

    }

  }

  private function hasController(&$parts, $module)
  {

      $this->path = $this->config->application . '/modules/' . $module . '/';


    if(! empty($parts[0]) && stream_resolve_include_path($this->path . $parts[0] . '.php')){

      return array_shift($parts);

    }

    return $this->config->PHIBER_CONTROLLER_DEFAULT;

  }

  private function hasAction(&$parts, $controller)
  {

    if(! empty($parts[0]) && method_exists($this->load($controller, null, $this->path, false), $parts[0])){

      return array_shift($parts);

    }

    array_shift($parts);
    return $this->config->PHIBER_CONTROLLER_DEFAULT_METHOD;

  }


  protected function dispatch()
  {

    $controller = $this->route['controller'];
    $action = $this->route['action'];

    $instance = $this->load($controller, null, $this->path);

    if(method_exists($instance, $action)){

      $instance->{$action}();
    }else{

      $instance->{$this->config->PHIBER_CONTROLLER_DEFAULT_METHOD}();

    }

  }




}
?>
