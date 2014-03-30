<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Hussein Guettaf <ghussein@coda-dz.com>
 * @package     Phiber
 */
namespace Phiber;

class phiber
{

  private $path = null;
  private $uri = null;
  private $phiber_flags;
  private $confFile = null;

  protected $_requestVars = array();

  protected $vars = array();

  protected $phiber_bootstrap;

  protected $keyHashes;

  protected function __construct($confFile = null)
  {

    spl_autoload_register(array($this, 'autoload'),true,true);

    if(null !== $confFile){
      $this->confFile = $confFile;
    }
    $this->phiber_bootstrap = \bootstrap::getInstance();
    $this->keyHashes = array('config'=>md5('Phiber.Config'),'view'=>md5('Phiber.View'));

  }

  protected function setLog($logger = null,$params = null,$name = null)
  {
    if(null === $logger){
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
    Session\session::start();

    Session\session::checkSession();

    if(\config::PHIBER_LOG){

      $this->register('errorLog',error::initiate($this->logger()));

    }

    $this->router();
    $this->getView();
    $this->plugins();
    $this->dispatch();
    $this->register('layoutEnabled', $this->config->layoutEnabled);
    $this->view->showTime();

  }

  protected function _redirect($url, $replace = true, $code = 307)
  {
    header("Location: $url", $replace, $code);
  }

  protected function plugins()
  {

    foreach($this->phiber_bootstrap->getPlugins() as $plugin){
      $this->load($plugin, null, $this->config->application . "/plugins/" . $plugin . "/")->run();
    }
  }

  protected function getView()
  {

    $path = array_slice($this->route, 0,3,true);

    $path = $this->config->application . "/modules/" . array_shift($path) . "/views/" . implode("/", $path) . ".php";

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
      include_once $this->config->application . "/layouts/layout.php";
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
      if(! empty($parts[0]) && $this->phiber_bootstrap->getModules()->isModule($parts[0])){

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
      $route = array('module' => 'default', 'controller' => 'index', 'action' => \config::PHIBER_CONTROLLER_DEFAULT_METHOD);
      $this->path = $this->config->application . '/modules/default/';

    }
    $this->register('route', $route);
  }
  private function setEnvVars()
  {

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
      $this->register('post', true);
      $this->register('get', false);
      $this->_requestVars = $_POST;
    }
    if(! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
      $this->register('ajax', true);
    }
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

  protected function isPost()
  {
    return Session\session::get('post');
  }

  protected function isGet()
  {
    return Session\session::get('get');
  }

  protected function isAjax()
  {
    return Session\session::get('ajax');
  }

  protected function dispatch()
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

  protected function _request($var, $default = null)
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

    $_SESSION['phiber'][$name] = $value;

  }

  protected function get($index)
  {
    if(isset($_SESSION['phiber'][$index])){

      return $_SESSION['phiber'][$index];

    }

  }
  protected function isGlobalFlagSet($flag)
  {
    return \Phiber\Flag\flag::_isset($flag, $this->get('phiber_flags'));
  }
  protected function isLocalFlagSet($flag,$identifier = null)
  {
    if(null === $identifier){
      $class = get_called_class();
      $identifier = sha1($class.get_parent_class($class).implode('',get_class_methods($class)));
    }
    return \Phiber\Flag\flag::_isset($flag, $this->get($identifier));
  }
  protected function setGlobalFlag($flag, $value)
  {
    $flags = $this->get('phiber_flags');
    \Phiber\Flag\flag::_set($flag, $value, $flags);
    $this->register('phiber_flags', $flags);
  }
  protected function setLocalFlag($flag, $value,$identifier = null)
  {
    if(null === $identifier){
      $class = get_called_class();
      $identifier = sha1($class.get_parent_class($class).implode('',get_class_methods($class)));
    }
    $flags = $this->get($identifier);
    \Phiber\Flag\flag::_set($flag, $value, $flags);
    $this->register($identifier, $flags);
  }
  protected function autoload($class)
  {

    $path = $this->config->library . '/';

    if('config' === $class && null !== $this->confFile){
      if(file_exists($this->confFile)){
        include_once $this->confFile;
        return;
      }else{
        trigger_error("Could not find configuration file: ".$this->confFile, E_USER_WARNING);
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


    if(in_array($class, array('config','view'))){

      $hash = $this->keyHashes[$class];

      if($this->isLoaded($hash) && false !== $inst){

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

  protected function isLoaded($hash)
  {
    if(isset($_SESSION['phiber'][$hash])){

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
   * @property object $config An instance of the config class
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

      case 'config':

        return $this->load('config');

    }
    if(key_exists($var, $this->vars)){
        return $this->vars[$var];
    }

  }

}
?>
