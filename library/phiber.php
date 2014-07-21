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
  private $routes = array();
  private $phiber_bootstrap;
  private $_requestVars = array();
  private $method;

  public function run($confFile = null)
  {

    spl_autoload_register(array($this, 'autoload'),true,true);

    if(null !== $confFile){
      $this->confFile = $confFile;

    }

    error::initiate($this->logger(),$this->config);

    date_default_timezone_set($this->config->PHIBER_TIMEZONE);

    $this->phiber_bootstrap = \bootstrap::getInstance($this->config);

    $this->session->start();

    $this->session->checkSession();

    $this->register('context', null);
    $this->register('layoutEnabled', $this->config->layoutEnabled);
    $this->register('ajax', false);

    $this->router($this->getRoutes());

    $this->plugins();
    $this->dispatch();
    $this->getView();
    $this->view->showTime();

  }
  private function getRoutes()
  {
    return (count($this->routes))?$this->routes:null;
  }
  public function addRoute(array $routes)
  {
    if(count($routes)){
      foreach($routes as $rule => $path){
        $this->routes[$rule] = $path;
      }

    }
  }
  public function addRoutesFile($path)
  {
    if(stream_resolve_include_path($path)){
      $routes = include $path;
      if(is_array($routes)){
        foreach($routes as $route){
          $this->addRoute($route);
        }
      }

    }

  }
  private function plugins()
  {

    foreach($this->phiber_bootstrap->getPlugins() as $plugin){
      $this->load($plugin, null, $this->config->application.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin .DIRECTORY_SEPARATOR)->run();
    }
  }

  private function router(array $routes = null)
  {


    $this->uri = urldecode($_SERVER['REQUEST_URI']);

    if(null !== $routes && ($this->routeMatchSimple($routes, $this->uri) || $this->routeMatchRegex($routes, $this->uri))){

      return;
    }
    $this->method = $_SERVER['REQUEST_METHOD'];

    $this->register('http_method', $this->method);
    if($this->isValidURI($this->uri)){
      $bootstrap = $this->phiber_bootstrap->getModules();
      $parts = explode("/", trim($this->uri));
      array_shift($parts);
      if(! empty($parts[0]) && $bootstrap->isModule($parts[0])){

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

      $route = array('module' => $module, 'controller' => $controller, 'action' => $action, 'vars' => $this->_requestVars);
    }else{
      $route = array('module' => 'default', 'controller' => 'index', 'action' => $this->config->PHIBER_CONTROLLER_DEFAULT_METHOD);
      $this->path = $this->config->application.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR;

    }

    if(! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
      $this->register('ajax', true);
    }
    $this->register('route', $route);
    $this->register('_request', $this->_requestVars);
  }

  private function routeMatchSimple($routes, $current)
  {
    if(strpos($current,'~') !== false){
      return;
    }
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
       if(strpos($def,'~') === false){
         continue;
       }
       if(preg_match_all($def, $current, $matches)){
         array_shift($matches);
         if(is_array($route)){
           if(isset($route['vars']) && is_array($route['vars'] )){

             foreach($route['vars'] as $key => $var){
               if(strpos($var, ':') !== false){
                 $route['vars'][ltrim($var,':')]= $matches[$key][0];
                 unset($route['vars'][$key]);
               }
             }
           }
           $this->route = $route;
         }elseif(strpos($route, ':') !== false){
           $route = trim($route,'/');
           $parts = explode('/',$route);
           $url = '';
           $pos = 0;
           foreach($parts as $k => $part){
             if(strpos($part, ':') !== false){

               $url .= ltrim($part,':').'/'.$matches[$pos][0].'/';
               $pos++;
             }else{
               $url .= $part.'/';
             }
           }
           $url = '/'.rtrim($url,'/');
           if($this->isValidURI($url)){
             $_SERVER['REQUEST_URI'] = $url;
             $this->router();
           }

         }elseif($this->isValidURI($route)){

           $_SERVER['REQUEST_URI'] = $route;
           $this->router();

         }

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

      $this->path = $this->config->application.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR;


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
