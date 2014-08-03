<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Hussein Guettaf <ghussein@coda-dz.com>
 * @package     Phiber
 */
namespace Phiber;


class phiber extends wire
{

  private $controller;
  private $path;
  private $uri;
  private $routes = array();
  private $_requestVars = array();
  private $plugins = array();

  public $method;
  public $libs = array();
  public $ajax = false;
  public $route;
  public $request;
  public $logger;

  private static $instance;

  const EVENT_BOOT = 'phiber.boot';

  const EVENT_SHUTDOWN = 'phiber.shutdown';

  const EVENT_DISPATCH = 'phiber.dispatch';

  public static function getInstance()
  {
    if(null !== self::$instance){
      return self::$instance;
    }

    return self::$instance = new self;
  }

  public function run()
  {

    error::initiate($this->logger(),$this->config);

    date_default_timezone_set($this->config->PHIBER_TIMEZONE);

    $this->session->start();

    Event\eventfull::notify(new Event\event(self::EVENT_BOOT, __class__));

    $this->session->checkSession();

    $this->router($this->getRoutes());

    $this->plugins();

    $this->dispatch();

    Event\eventfull::notify(new Event\event(self::EVENT_DISPATCH, __class__));

    $this->getView();

    $this->view->showTime();

    Event\eventfull::notify(new Event\event(self::EVENT_SHUTDOWN, __class__));

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
  private function getPlugins()
  {


    $path = $this->config->application . DIRECTORY_SEPARATOR.'plugins';
    $plugins = $path.DIRECTORY_SEPARATOR.'plugins.php';

    if(stream_resolve_include_path($plugins)){
      $pluginsList = include $plugins;
    }else{
      $pluginsList['g5475ff2f44a9102d8b7'] = 'phiber';
    }

    if($this->config->PHIBER_MODE === 'production' && stream_resolve_include_path($plugins) && is_array($pluginsList)){
      return $pluginsList;
    }

    /*
     * //Auto discovery
    *
    */

    foreach (new \DirectoryIterator($path) as $plugin) {
      if ($plugin->isDot()) {
        continue;
      }
      $dir = $path . "/" . $plugin->getFilename();
      if (is_dir($dir)) {
        $this->plugins[] = $plugin->getFilename();
      }
    }

    if(count(array_diff($pluginsList,$this->plugins)) !== 0 || count(array_diff($this->plugins,$pluginsList)) !== 0 || isset($pluginsList['g5475ff2f44a9102d8b7'])){
      $code = '<?php return '.\tools::transcribe($this->plugins).'; ?>';
      file_put_contents($plugins, $code);

    }

    return $this->plugins;

  }
  private function plugins()
  {
    $plugins = $this->getPlugins();
    if(count($plugins)){
      foreach($plugins as $plugin){
        include $this->config->application.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin .DIRECTORY_SEPARATOR.$plugin.'.php';
        $plugin::getInstance()->run();
      }
    }

  }

  private function router(array $routes = null)
  {


    $this->uri = urldecode($_SERVER['REQUEST_URI']);

    if(null !== $routes && ($this->routeMatchSimple($routes, $this->uri) || $this->routeMatchRegex($routes, $this->uri))){

      return;
    }
    $this->method = $_SERVER['REQUEST_METHOD'];

    if($this->isValidURI($this->uri)){

      $parts = explode("/", trim($this->uri));
      unset($parts[0]);
      if(! empty($parts[0]) && is_dir($this->config->application.'/modules/'.$parts[0])){

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
      $this->ajax = true;
    }
    $this->route = $route;
    $this->request = $this->_requestVars;
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
    $needles = array('/?','?','/?','&','=');
    $replace = array('/','/?','/','/','/',);
    $uri = str_replace($needles, $replace, $uri);


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
      $controller = $this->config->PHIBER_CONTROLLER_DEFAULT;

    if(! empty($parts[0]) && stream_resolve_include_path($this->path . $parts[0] . '.php')){

      $controller = array_shift($parts);

    }
    $this->controller = $this->loadController($controller);

    return $controller;

  }

  private function hasAction(&$parts, $controller)
  {

    if(! empty($parts[0]) && method_exists($this->controller, $parts[0])){

      return array_shift($parts);

    }

    array_shift($parts);
    return $this->config->PHIBER_CONTROLLER_DEFAULT_METHOD;

  }


  private function dispatch()
  {

    $controller = $this->route['controller'];
    $action = $this->route['action'];

    if(method_exists($this->controller,$action)){
      $this->controller->{$action}();
    }

  }

  private function loadController($controller)
  {
    require $this->path.$controller.'.php';
    return $controller::getInstance();
  }

  public static function getEvents()
  {
    return array(self::EVENT_BOOT, self::EVENT_DISPATCH, self::EVENT_SHUTDOWN);
  }
}
?>
