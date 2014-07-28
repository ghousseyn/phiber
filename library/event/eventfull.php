<?php
namespace Phiber\Event;

use Phiber\Interfaces\phiberEventSubject;
use Phiber\Interfaces\phiberEventObserver;
use Phiber\Session\session;
use Phiber\Event\event;

class eventfull implements phiberEventSubject
{
  public static function getEvents()
  {
    return array();
  }

  private static $event_listeners_namespace = 'phiber_event_listeners';


  public static function attach($observer, $event = null)
  {

    if(null !== $event){
      if(strpos($event,'.') === false){
        foreach($event::getEvents() as $event){
          self::attach($observer,$event);
        }
        return;
      }
      $parts = explode('.',$event);
      if(!session::nsExists(self::$event_listeners_namespace)){
        session::set(sha1($observer), array($parts[0] => array($parts[1]=>$event)),self::$event_listeners_namespace);
        session::set(sha1($observer), array('object' => $observer),self::$event_listeners_namespace);

      }else{
        if(!session::exists(sha1($observer), self::$event_listeners_namespace)){
          session::set(sha1($observer), array('object' => $observer),self::$event_listeners_namespace);
        }
        session::append(sha1($observer), array($parts[0] => array($parts[1],$event)),self::$event_listeners_namespace,true);
      }

    }else{
      foreach(static::getEvents() as $event){
        self::attach($observer,$event);
      }
    }

  }
  public static function detach($observer, $event = null)
  {
    if(null !== $event){
      if(strpos($event,'.') === false){
        foreach($event::getEvents() as $event){
          self::detach($observer,$event);
        }
        return;
      }
      $parts = explode('.',$event);
      if(isset($_SESSION[self::$event_listeners_namespace][sha1($observer)][$parts[0]])){
        $path = $_SESSION[self::$event_listeners_namespace][sha1($observer)][$parts[0]];
      }

      $path[$parts[1]] = null;

      $path = array_filter($path,'strlen');
      if(count($path)){
        $_SESSION[self::$event_listeners_namespace][sha1($observer)][$parts[0]] = $path;
        return;
      }
    }

    session::delete(sha1($observer), self::$event_listeners_namespace);

  }
  public static function notify(event $event)
  {

    if(session::isStarted() && is_array(session::getNS(self::$event_listeners_namespace))){
      foreach(session::getNS(self::$event_listeners_namespace) as $observer){
        $obs = $observer['object'];
        $observer['object'] = new $obs;
        if(isset($observer[strstr($event->current['event'],'.',true)]) && in_array($event->current['event'], $observer[strstr($event->current['event'],'.',true)]))
        $observer['object']->update($event);
      }
    }

  }
}

?>