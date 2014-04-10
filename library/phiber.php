<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Hussein Guettaf <ghussein@coda-dz.com>
 * @package     Phiber
 */
namespace Phiber;

use Phiber\Flag\flag;

use Phiber\flag\httpMethod;

class phiber
{

  const SESSION_NAMESPACE = 'phiber';
  private $path;
  private $uri;
  private $phiber_flags;
  private $confFile;
  private $phiber_bootstrap;
  protected $keyHashes;
  private $method;

  protected $_requestVars = array();
  protected $vars = array();

  private function __construct()
  {
    $this->keyHashes = array('view'=>md5('Phiber.View'));
  }

  protected function setLog($logger = null,$params = null,$name = null)
  {
    if(null === $logger || !file_exists($this->config->library.'/logger/'.$logger.'.php')){
      $logger = \config::PHIBER_LOG_DEFAULT_HANDLER;
    }
    if(null === $params){
      $params = array('default',$this->config->logDir.'/'.\config::PHIBER_LOG_DEFAULT_FILE);
    }
    if(!is_array($params)){
      $params = array($params,$this->config->logDir.'/'.$params.'.log');
    }
    $logWriter = "Phiber\\Logger\\$logger";
    $writer = new $logWriter($params);
    if(null === $name){
      $name = 'log';
    }
    $writer->level = $this->config->logLevel;
    $this->register(sha1($name),array($logger,$params));
    return $writer;
  }
  protected function logger($name = 'log')
  {
    $name = sha1($name);
    if(Session\session::exists($name)){
      $log = $this->get($name);
      $class = "Phiber\\Logger\\$log[0]";
      if(file_exists($this->config->library.'/logger/'.$class.'.php')){
        $logObject = new $class;
        return ($logObject instanceof Logger\logger)? $logObject : $this->logger();
      }

    }
    return $this->setLog();
  }

  public static function getInstance()
  {
    return new static();
  }

  public function run($confFile = null)
  {
    spl_autoload_register(array($this, 'autoload'),true,true);

    if(null !== $confFile){
      $this->confFile = $confFile;
    }

    $this->phiber_bootstrap = \bootstrap::getInstance();


    Session\session::start();

    Session\session::checkSession();

    if(\config::PHIBER_LOG){

      error::initiate($this->logger());

    }

    $this->register('context', null);
    $this->register('layoutEnabled', $this->config->layoutEnabled);

    $this->whatMethod();
    $this->router();
    $this->getView();
    $this->plugins();
    $this->dispatch();

    $this->view->showTime();

  }
  private function whatMethod()
  {
    $this->method = $_SERVER['REQUEST_METHOD'];

    $this->register('http_method', $this->method);

  }

  protected function _redirect($url, $replace = true, $code = 307)
  {
    header("Location: $url", $replace, $code);
  }

  protected function plugins()
  {

    foreach($this->phiber_bootstrap->getPlugins() as $plugin){
      $this->load($plugin, null, $this->config->application . '/plugins/' . $plugin . '/')->run();
    }
  }

  protected function getView()
  {

    $path = array_slice($this->route, 0,3,true);

    $path = $this->config->application . '/modules/' . array_shift($path) . '/views/' . implode('/', $path) . '.php';

    $this->view->viewPath = $path;

  }
  protected function setView($path)
  {
    if(file_exists($path)){
      $this->view->viewPath = $path;
      return true;
    }
    return false;
  }

  protected function renderLayout($layout = null)
  {
    if(null !== $layout){
      if(file_exists($layout)){
        include_once $layout;
      }

    }else{
      include_once $this->config->application . '/layouts/layout.php';
    }
  }

  protected function router()
  {
    $this->uri = urldecode($_SERVER['REQUEST_URI']);

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

  protected function isValidURI(&$uri)
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

  private function isHttpMethod($method)
  {
    return (strtoupper($method) === strtoupper($this->get('http_method')));
  }
  protected function isPost()
  {
    return $this->isHttpMethod('post');
  }

  protected function isGet()
  {
    return $this->isHttpMethod('get');
  }

  protected function isPut()
  {
    return $this->isHttpMethod('put');
  }

  protected function isHead()
  {
    return $this->isHttpMethod('head');
  }

  protected function isDelete()
  {
     return $this->isHttpMethod('delete');
  }
  protected function isOptions()
  {
    return $this->isHttpMethod('options');
  }

  protected function isTrace()
  {
    return $this->isHttpMethod('trace');
  }
  protected function isAjax()
  {
    return Session\session::get('ajax');
  }

  private function dispatch()
  {

    $controller = $this->route['controller'];
    $action = $this->route['action'];

    $instance = $this->load($controller, null, $this->path);

    if(method_exists($instance, $action)){

      $instance->{$action}();
    }else{

      $instance->{\config::PHIBER_CONTROLLER_DEFAULT_METHOD}();

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

  protected function _requestParam($var, $default = null)
  {
    $vars = $this->get('_request');
    if(is_array($vars) && isset($vars[$var])){
      return $vars[$var];
    }else{
      if(null !== $default){
        return $default;
      }
    }

  }

  protected function setVars($parts)
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


    if(! empty($parts[0]) && file_exists($this->path . $parts[0] . '.php')){

      return array_shift($parts);

    }

    return \config::PHIBER_CONTROLLER_DEFAULT;

  }

  private function hasAction(&$parts, $controller)
  {

    if(! empty($parts[0]) && method_exists($this->load($controller, null, $this->path, false), $parts[0])){

      return array_shift($parts);

    }

    array_shift($parts);
    return \config::PHIBER_CONTROLLER_DEFAULT_METHOD;

  }

  protected function register($name, $value)
  {

    Session\session::set($name, $value);

  }

  protected function get($index)
  {
    return Session\session::get($index);
  }
  protected function isFlagSet($flag)
  {
    return \Phiber\Flag\flag::_isset($flag, $this->get('phiber_flags'));
  }

  protected function setFlag($flag, $value)
  {
    \Phiber\Flag\flag::_set($flag, $value, $_SESSION[self::SESSION_NAMESPACE]['phiber_flags']);
  }


  protected function autoload($class)
  {

    $path = $this->config->library . '/';

    if('config' === $class && null !== $this->confFile){
      if(file_exists($this->confFile)){
        include_once $this->confFile;
        return;
      }else{
        trigger_error("Could not find configuration file: ".$this->confFile, E_USER_ERROR);
      }

    }
    if(! strstr($class, '\\')){

      if(file_exists($path . $class . '.php')){

        include_once $path . $class . '.php';
        return;
      }

      $module = $this->config->application.'/modules/'.$this->route['module'].'/';

      if(file_exists($module . $class . '.php')){

        include_once $module . $class . '.php';

      }
      return;
    }

    $parts = explode('\\', $class);


    $count = count($parts);
      if($parts[0] !== 'Phiber'){

        $path = $this->config->application . '/';
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


    if(in_array($class, array('view'))){

      $hash = $this->keyHashes[$class];

      if(Session\session::exists($hash) && false !== $inst){

        return $this->get($hash);
      }
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

    if(isset($hash)){
      $this->register($hash, $instance);
    }
    return $instance;
  }

  public function __set($var, $val)
  {

    $this->vars[$var] = $val;

  }

  /**
   * @property object $view An instance of the view class
   * @property array $route Current route
   * @property string $content The path to the selected template (partial view)
   * @property object $config An instance of the config class
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

      case 'config':

        return $this->load('config');

    }
    if(key_exists($var, $this->vars)){
        return $this->vars[$var];
    }

  }

}
?>
