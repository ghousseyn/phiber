<?php
namespace Phiber\Event;

use Phiber\Interfaces\phiberEventSubject;
use Phiber\Interfaces\phiberEventObserver;
use Phiber\Session\session;
use Phiber\Event\event;

abstract class eventfull implements phiberEventSubject
{
  abstract public static function getEvents();

  private static $event_listeners_namespace = 'phiber_event_listeners';

  public static function attach(phiberEventObserver $observer, $event = null)
  {

    if(null !== $event){
      $parts = explode('.',$event);
      if(!session::nsExists(self::$event_listeners_namespace)){
        session::set(sha1($observer->__toString()), array($parts[0] => array($parts[1]=>$event)),self::$event_listeners_namespace);
        session::set(sha1($observer->__toString()), array('object' => $observer),self::$event_listeners_namespace);

      }else{
        if(!session::exists(sha1($observer->__toString()), self::$event_listeners_namespace)){
          session::set(sha1($observer->__toString()), array('object' => $observer),self::$event_listeners_namespace);
        }
        session::append(sha1($observer->__toString()), array($parts[0] => array($parts[1],$event)),self::$event_listeners_namespace,true);
      }

    }else{
      foreach(static::getEvents() as $event){
        self::attach($observer,$event);
      }
    }

  }
  public static function detach(phiberEventObserver $observer, $event = null)
  {
    if(null !== $event){
      $parts = explode('.',$event);

      $path = $_SESSION[self::$event_listeners_namespace][sha1($observer->__toString())][$parts[0]];

      $path[$parts[1]] = null;

      $path = array_filter($path,'strlen');
      if(count($path)){
        $_SESSION[self::$event_listeners_namespace][sha1($observer->__toString())][$parts[0]] = $path;
        return;
      }
    }

    session::delete(sha1($observer->__toString()), self::$event_listeners_namespace);

  }
  public static function notify(event $event)
  {
    if(session::isStarted() && is_array(session::getNS(self::$event_listeners_namespace))){
      foreach(session::getNS(self::$event_listeners_namespace) as $observer){
        if(isset($observer[strstr($event->current['event'],'.',true)]) && in_array($event->current['event'], $observer[strstr($event->current['event'],'.',true)]))
        $observer['object']->update($event);
      }
    }

  }
}

?>