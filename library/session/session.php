<?php
/**
 * Session class.
 * @version 	1.0
 * @author 	Housseyn Guettaf <ghoucine@gmail.com>
 * @package 	Phiber
 */
namespace Phiber\Session;

class session
{
  public static $namespace = 'phiber';

  private static $instance;

  const EVENT_REGEN = 'session.regenerate';

  const EVENT_DESTR = 'session.destroy';

  public function start()
  {
    session_start();

    if(!isset( $_SESSION[self::$namespace]['user']['name'])){
      $_SESSION[self::$namespace]['user']['name'] = 'guest';

    }
    if(!isset( $_SESSION[self::$namespace]['user']['created'])){
      $_SESSION[self::$namespace]['user']['created'] = time();

    }
    $_SESSION[self::$namespace]['user']['activity'] = time();

  }
  public function check()
  {

      if(isset($_SESSION[self::$namespace]['user']['activity']) && (time() - $_SESSION[self::$namespace]['user']['activity'] > \Phiber\tools::orDefault((int) \Phiber\config::getInstance()->PHIBER_SESSION_INACTIVE, 1800))){

        $this->destroy();

      }


      if(isset($_SESSION[self::$namespace]['user']['created']) && (time() - $_SESSION[self::$namespace]['user']['created'] > \Phiber\tools::orDefault((int) \Phiber\config::getInstance()->PHIBER_SESSION_REGENERATE, 1800))){

        $this->regenerate();

      }


  }
  public function destroy($authority = null)
  {
    if(!session_destroy()){
      trigger_error("Session could not be destroyed!",E_USER_NOTICE);
      return false;
    }
    if(null === $authority){
      $authority = __METHOD__;
    }
    \Phiber\Event\eventfull::notify(new \Phiber\Event\event(self::EVENT_DESTR,  $authority));
    return true;
  }
  public function regenerate()
  {
    if(!session_regenerate_id(true)){

      trigger_error("Session ID could not be regenerated!",E_USER_NOTICE);
      return false;
    }

    $_SESSION[self::$namespace]['user']['created'] = time();

    \Phiber\Event\eventfull::notify(new \Phiber\Event\event(self::EVENT_REGEN,  __METHOD__));
    return true;
  }
  public function isStarted()
  {
    return isset($_SESSION);
  }
  public function get($index, $namespace = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    if(isset($_SESSION[$namespace][$index])){

      return $_SESSION[$namespace][$index];

    }

  }
  public function getNS($namespace)
  {
    if(isset($_SESSION[$namespace])){

      return $_SESSION[$namespace];

    }
  }
  public function setNS($namespace,$value)
  {
    $_SESSION[$namespace] = $value;
  }
  public function set($index, $value, $namespace = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    $_SESSION[$namespace][$index] = $value;

  }

  public function delete($index, $namespace = null, $path = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    if(self::exists($index,$namespace)){
      unset($_SESSION[$namespace][$index]);
      return true;
    }
    return false;
  }
  public function append($index, $value, $namespace = null, $asArray = false)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;
    $key = key($value);
    if($asArray){
      // $value = array( key => array( key , value ) )
      $_SESSION[$namespace][$index][$key][$value[$key][0]] = $value[$key][1];
    }else{
      $_SESSION[$namespace][$index][] = $value;
    }


  }
  public function exists($index, $namespace = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    return isset($_SESSION[$namespace][$index]);
  }
  public function nsExists($namespace)
  {
    return isset($_SESSION[$namespace]);
  }

  public static function getEvents()
  {
    return array(self::EVENT_DESTR,self::EVENT_REGEN);
  }
  public static function getInstance()
  {
    if(null !== self::$instance){
      return self::$instance;
    }
    return self::$instance = new self;
  }
}

?>