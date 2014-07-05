<?php
namespace Phiber\Session;

use Phiber\Event\event;
use Phiber\Event\eventfull;

class session extends eventfull
{
  public static $namespace = 'phiber';

  const EVENT_REGEN = 'session.regenerate';

  const EVENT_DESTR = 'session.destroy';

  public static function start()
  {
    session_start();

    if(!isset( $_SESSION[self::$namespace]['user']['name'])){
      $_SESSION[self::$namespace]['user']['name'] = 'guest';
      self::regenerate();
    }
    if(!isset( $_SESSION[self::$namespace]['user']['created'])){
      $_SESSION[self::$namespace]['user']['created'] = time();

    }
    $_SESSION[self::$namespace]['user']['activity'] = time();

  }
  public static function checkSession()
  {

      if(isset($_SESSION[self::$namespace]['user']['activity']) && (time() - $_SESSION[self::$namespace]['user']['activity'] > \tools::orDefault((int) \config::getInstance()->PHIBER_SESSION_INACTIVE, 1800))){

        //self::destroy();

      }


      if(isset($_SESSION[self::$namespace]['user']['created']) && (time() - $_SESSION[self::$namespace]['user']['created'] > \tools::orDefault((int) \config::getInstance()->PHIBER_SESSION_REGENERATE, 1800))){

       // self::regenerate();

      }


  }
  public static function destroy($authority = null)
  {
    if(!session_destroy()){
      trigger_error("Session could not be destroyed!",E_USER_NOTICE);
      return false;
    }
    if(null === $authority){
      $authority = __METHOD__;
    }
    self::notify(new event(self::EVENT_DESTR,  $authority));
    return true;
  }
  public static function regenerate()
  {
    if(!session_regenerate_id(true)){

      trigger_error("Session ID could not be regenerated!",E_USER_NOTICE);
      return false;
    }

    $_SESSION['user']['created'] = time();

    self::notify(new event(self::EVENT_REGEN,  __METHOD__));
    return true;
  }
  public static function isStarted()
  {
    return isset($_SESSION);
  }
  public static function get($index, $namespace = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    if(isset($_SESSION[$namespace][$index])){

      return $_SESSION[$namespace][$index];

    }

  }
  public static function getNS($namespace)
  {
    if(isset($_SESSION[$namespace])){

      return $_SESSION[$namespace];

    }
  }
  public static function setNS($namespace,$value)
  {
    $_SESSION[$namespace] = $value;
  }
  public static function set($index, $value, $namespace = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    $_SESSION[$namespace][$index] = $value;

  }

  public static function delete($index, $namespace = null, $path = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    if(self::exists($index,$namespace)){
      unset($_SESSION[$namespace][$index]);
      return true;
    }
    return false;
  }
  public static function append($index, $value, $namespace = null, $asArray = false)
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
  public static function exists($index, $namespace = null)
  {
    $namespace = (isset($namespace))?$namespace:self::$namespace;

    return isset($_SESSION[$namespace][$index]);
  }
  public static function nsExists($namespace)
  {
    return isset($_SESSION[$namespace]);
  }

  public static function getEvents()
  {
    return array(self::EVENT_DESTR,self::EVENT_REGEN);
  }

}

?>