<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Hussein Guettaf <ghussein@coda-dz.com>
 * @package     Phiber
 */
namespace Phiber;

class main
{

  private $path = null;
  private $uri = null;

  protected $_requestVars = array();

  protected $vars = array();

  protected function __construct()
  {
    spl_autoload_register(array($this, 'autoload'),true,true);

    if($this->conf->log){
      $this->register('errorLog',error::initiate($this->logger()));
    }

    $this->register('layoutEnabled', $this->conf->layoutEnabled);
    if(! isset($_SESSION)){
      session_start();
      $_SESSION['user']['activity'] = time();
    }

  }

  protected function setLog($logger = null,$params = null,$name = null)
  {
    if(null === $logger){
      $logger = $this->conf->logHandler;
    }
    if(null === $params){
      $params = array('default',$this->conf->logDir.'/'.$this->conf->logFile);
    }
    if(!is_array($params)){
      $params = array($params,$this->conf->logDir.'/'.$params.'.log');
    }
    $logWriter = "Phiber\\Logger\\$logger";
    $writer = new $logWriter($params);
    if(null === $name){
      $name = 'log';
    }
    $writer->level = $this->conf->logLevel;
    $this->register($name,$writer);
    return $writer;
  }
  protected function getLog($name)
  {
    if($this->isLoaded($name)){
      $log = $this->get($name);
      return ($log instanceof Logger\logger)? $log : $this->logger();
    }
  }
  protected function logger()
  {
    if($this->isLoaded('log')){
      return $this->get('log');
    }
    return $this->setLog();
  }
  public static function getInstance()
  {
    return new static();
  }

  public function run()
  {

    $this->checkSession();
    $this->router();
    $this->getView();
    $this->plugins();
    $this->dispatch();
    if($this->conf->debug){
      $this->debug->output();
    }
    $this->view->showTime();

  }

  protected function _redirect($url, $replace = true, $code = 307)
  {
    header("Location: $url", $replace, $code);
  }

  protected function plugins()
  {

    foreach($this->bootstrap->getPlugins() as $plugin){
      $this->load($plugin, null, $this->conf->application . "/plugins/" . $plugin . "/")->run();
    }
  }

 protected function checkSession()
  {
    if($this->conf->inactive){
      if(isset($_SESSION['user']['activity']) && (time() - $_SESSION['user']['activity'] > $this->load('tools')->orDefault((int) $this->conf->inactive, 1800))){

        // session_unset();
        session_destroy();
      }
    }
    if($this->conf->sessionReginerate){
      if(isset($_SESSION['user']['created']) && (time() - $_SESSION['user']['created'] > $this->tools->orDefault((int) $this->conf->sessionReginerate, 1800))){
        session_regenerate_id(true);
        $_SESSION['user']['created'] = time();
      }
    }

  }

  protected function getView()
  {

    $path[] = $this->route['module'];
    $path[] = $this->route['controller'];
    $path[] = $this->route['action'];

      $path = $this->conf->application . "/modules/" . array_shift($path) . "/views/" . implode("/", $path) . ".php";

    $this->view->viewPath = $path;

  }

  protected function renderLayout($layout = null)
  {
    if(null !== $layout){
      if(file_exists($layout)){
        include_once $layout;
      }

    }else{
      include_once $this->conf->application . "/layouts/layout.php";
    }
  }

  protected function router()
  {
    $this->uri = urldecode($_SERVER['REQUEST_URI']);
    $this->register('post', false);
    $this->register('get', true);
    $this->register('ajax', false);
    $this->register('context', null);
    if($this->isValidURI($this->uri)){
      $parts = explode("/", trim($this->uri));
      array_shift($parts);
      if(! empty($parts[0]) && $this->bootstrap->isModule($parts[0])){

        $module = array_shift($parts);
        $controller = $this->hasController($parts, $module);
        $action = $this->hasAction($parts, $controller);

      }else{

        $module = "default";
        array_shift($parts);
        $controller = $this->hasController($parts, $module);
        $action = $this->hasAction($parts, $controller);

      }
      if(count($parts)){
        $this->setVars($parts);
      }
      $this->setEnvVars();

      $route = array('module' => $module, 'controller' => $controller, 'action' => $action, 'vars' => $this->get('_request'));
    }else{
      $route = array('module' => 'default', 'controller' => 'index', 'action' => $this->conf->defaultMethod);

    }
    $this->register('route', $route);
  }
  private function setEnvVars()
  {
    $this->register('_request', $this->_requestVars);

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
      $this->register('post', true);
      $this->register('get', false);
      $this->_requestVars = $_POST;
    }
    if(! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
      $this->register('ajax', true);
    }

  }
  protected function isValidURI(&$uri)
  {
    $uri = str_replace('/?', '/', $uri);
    $uri = str_replace('?', '/?', $uri);
    $uri = str_replace('/?', '/', $uri);
    $uri = str_replace('&', '/', $uri);
    $uri = str_replace('=', '/', $uri);

    if(preg_match('~^(?:[/\\w\\s-\,\$\.\*\!\'\(\)\~]+)+/?$~', $uri)){
      return true;
    }
    return false;
  }

  protected function isPost()
  {
    return $this->get('post');
  }

  protected function isGet()
  {
    return $this->get('get');
  }

  protected function isAjax()
  {
    return $this->get('ajax');
  }

  protected function dispatch()
  {
    // $mod = $this->route["module"];
    $controller = $this->route['controller'];
    $action = $this->route['action'];

    $instance = $this->load($controller, null, $this->path);

    if(method_exists($instance, $action)){

      $instance->{$action}();
    }else{

      $instance->{$this->conf->defaultMethod}();

    }

  }

  protected function contextSwitch($context)
  {
    if($context === 'html' || $context === 'json'){

      $this->register('context', $context);
      $this->register('layoutEnabled', false);

    }
    if($context === 'json'){
      $this->view->viewPath = null;
    }

  }

  protected function _request($var, $default = null)
  {
    $vars = $this->get('_request');
    if(is_array($vars) && isset($vars[$var])){
      return $vars[$var];
    }
    if(null !== $default){
      return $default;
    }

  }

  protected function setVars($parts)
  {
    foreach($parts as $k => $val){
      if($k == 0 || ($k % 2) == 0){
        $this->_requestVars[$parts[$k]] = $parts[$k + 1];
      }

    }

  }

  private function hasController(&$parts, $module)
  {

      $this->path = $this->conf->application . '/modules/' . $module . '/';


    if(! empty($parts[0]) && file_exists($this->path . $parts[0] . '.php')){

      return array_shift($parts);

    }

    return 'index';

  }

  private function hasAction(&$parts, $controller)
  {

    if(! empty($parts[0]) && method_exists($this->load($controller, null, $this->path, false), $parts[0])){

      return array_shift($parts);

    }

    array_shift($parts);
    return $this->conf->defaultMethod;

  }

  protected function register($name, $value)
  {

    $_SESSION[$name] = $value;

  }

  protected function get($index)
  {
    if(isset($_SESSION[$index])){

      return $_SESSION[$index];

    }

  }

  protected function autoload($class)
  {

    $path = $this->conf->library . '/';

    if(! strstr($class, '\\')){

      if(file_exists($path . $class . '.php')){

        include_once $path . $class . '.php';
        return;
      }

      $module = $this->conf->application.'/modules/'.$this->route['module'].'/';

      if(file_exists($module . $class . '.php')){

        include_once $module . $class . '.php';

      }
      return;
    }

    $parts = explode('\\', $class);


    $count = count($parts);
      if($parts[0] !== 'Phiber'){

        $path = $this->conf->application . '/';
      }
    for($i=0; $i < $count; $i++){
      if($parts[$i] === 'Phiber'){
        continue;
      }
      if($i == $count - 1){
        $path .= $parts[$i] . '.php';
        break;
      }
      $path .=  strtolower($parts[$i]) . '/';

    }

    if(file_exists($path)){
      include_once $path;
      return;
    }



  }

  protected function load($class, $params = null, $path = null, $inst = true)
  {
    $newpath = $path;
    if(null === $newpath){
      $newpath = __DIR__ . '/';
    }
    $incpath = $newpath . $class . '.php';

    $hash = hash('adler32', $incpath);
    if($this->isLoaded($hash) && false !== $inst){
      return $this->get($hash);
    }
    if(! file_exists($incpath)){
      return false;
    }
    include_once ($incpath);
    if(false === $inst){
      return $class;
    }
    if(null !== $params && is_array($params)){
      $parameters = implode(",", $params);
    }else{
      $parameters = $params;
    }
    $instance = $class::getInstance($parameters);
    $serialisable = array('config', 'debug', 'tools', 'view', 'bootstrap');
    if(in_array($class, $serialisable)){
      $this->register($hash, $instance);
    }
    return $instance;
  }

  protected function isLoaded($hash)
  {
    if(isset($_SESSION[$hash])){

      return true;

    }

    return false;

  }

  public function __set($var, $val)
  {

    $this->vars[$var] = $val;

  }

  /**
   * @property object $view An instance of the view class
   * @property array $route Current route
   * @property string $content The path to the selected template (partial view)
   * @property object $conf An instance of the config class
   * @property object $bootstrap An instance of the the bootstrap class
   * @property object $tools An instance of the tools class
   * @property object $debug An instance of the debug class
   * @param string $var Property name
   */
  public function __get($var)
  {

    switch($var){

      case 'view':

        return $this->load('view');

      case 'route':

        return $this->get('route');

      case 'content':

        return $this->viewPath;

      case 'conf':

        return $this->load('config');

      case 'bootstrap':

        return $this->load('bootstrap');

      case 'tools':

        return $this->load('tools');


    }
    if(key_exists($var, $this->vars)){
        return $this->vars[$var];
    }

  }

}
?>
