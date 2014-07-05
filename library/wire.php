<?php
namespace Phiber;

abstract class wire
{

  const PHIBER_SESSION_NAMESPACE = 'phiber';


  protected $vars = array();
  protected $keyHashes;
  protected $confFile;

  protected function __construct()
  {
    $this->keyHashes = array('view'=>md5('Phiber.View'));

  }
  public static function getInstance()
  {
    return new static();
  }


  protected function _redirect($url, $replace = true, $code = 307)
  {
    header("Location: $url", $replace, $code);
  }

  protected function getView()
  {

    $path = array_slice($this->route, 0,3,true);

    $path = $this->config->application . '/modules/' . array_shift($path) . '/views/' . implode('/', $path) . '.php';

    $this->view->viewPath = $path;

  }
  protected function setView($path)
  {
    if(stream_resolve_include_path($path)){
      $this->view->viewPath = $path;
      return true;
    }
    return false;
  }
  protected function renderLayout()
  {
    require $this->config->application . '/layouts/layout.php';
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
    $flags = Session\session::get('phiber_flags');
    \Phiber\Flag\flag::_set($flag, $value, $flags);
    Session\session::set('phiber_flags', $flags);
  }

  protected function setLog($logger = null,$params = null,$name = null)
  {
    if(null === $logger || !stream_resolve_include_path($this->config->library.'/logger/'.$logger.'.php')){
      $logger = $this->config->PHIBER_LOG_DEFAULT_HANDLER;
    }
    if(null === $params){
      $params = array('default',$this->config->logDir.'/'.$this->config->PHIBER_LOG_DEFAULT_FILE);
    }
    if(!is_array($params)){
      $params = array($params,$this->config->logDir.'/'.$params.'.log');
    }
    $logWriter = "Phiber\\Logger\\$logger";
    $writer = new $logWriter($params,$this->config->logLevel);
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
      if(stream_resolve_include_path($this->config->library.'/logger/'.$log[0].'.php')){
        $logObject = new $class($log[1],$this->config->logLevel);
        return ($logObject instanceof Logger\logger)? $logObject : $this->logger();
      }

    }
    return $this->setLog();
  }
  protected function sendJSON($data, $options = 0 , $depth = 512)
  {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 16 Jul 1997 02:00:00 GMT');
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($data);
    exit(0);
  }

  protected function autoload($class)
  {



    if('config' === $class && null !== $this->confFile){

      if(stream_resolve_include_path($this->confFile)){
        require_once $this->confFile;
        return;
      }else{
        trigger_error("Could not find configuration file: ".$this->confFile, E_USER_ERROR);
      }

    }
    $path = $this->config->library . '/';
    if(! strstr($class, '\\')){

      if(stream_resolve_include_path($path . $class . '.php')){

        require_once $path . $class . '.php';
        return;
      }

      $module = $this->config->application.'/modules/'.$this->route['module'].'/';

      if(stream_resolve_include_path($module . $class . '.php')){

        require_once $module . $class . '.php';

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

    if(stream_resolve_include_path($path)){
      require_once $path;
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
    if('config' === $class && null !== $this->confFile){

     if(stream_resolve_include_path($this->confFile)){
      require_once $this->confFile;

      return \config::getInstance();
    }
    }

    if(in_array($class, array('view'))){

      $hash = $this->keyHashes[$class];

      if(Session\session::exists($hash) && false !== $inst){

        return $this->get($hash);
      }
    }

    if(! stream_resolve_include_path($incpath)){
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

        return \config::getInstance();

    }
    if(key_exists($var, $this->vars)){
      return $this->vars[$var];
    }

  }
}

?>