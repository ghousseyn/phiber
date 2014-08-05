<?php
namespace Phiber\Event;

use Phiber\Interfaces\phiberEventSubject;
use Phiber\Interfaces\phiberEventObserver;
use Phiber\Event\event;

class eventfull implements phiberEventSubject
{
  public static function getEvents()
  {
    return array();
  }

  public static function attach($observer, $event = null)
  {

    if(null !== $event){
      if(strpos($event,'.') === false){
        foreach($event::getEvents() as $event){
          self::attach($observer,$event);
        }
        return;
      }
      $observers = \Phiber\wire::getInstance()->getObservers();
      $parts = explode('.',$event);

      $observers[sha1($observer)]['object'] = $observer;
      $observers[sha1($observer)][$parts[0]][$parts[1]] = $event;

      \Phiber\wire::getInstance()->addObserver(sha1($observer), $observers[sha1($observer)]);
    }else{
      foreach(static::getEvents() as $event){
        self::attach($observer,$event);
      }
    }

  }
  public static function detach($observer, $event = null)
  {
    $observers = \Phiber\wire::getInstance()->getObservers();
    if(!isset($observers)){
      return false;
    }
    if(null !== $event){
      if(strpos($event,'.') === false){
        foreach($event::getEvents() as $event){
          self::detach($observer,$event);
        }
        return;
      }
      $parts = explode('.',$event);
      if(isset($observers[sha1($observer)][$parts[0]])){
        $path = $observers[sha1($observer)][$parts[0]];
      }else{
        return false;
      }

      $path[$parts[1]] = null;

      $path = array_filter($path,'strlen');
      if(count($path)){
        $observers[sha1($observer)][$parts[0]] = $path;
        \Phiber\wire::getInstance()->addObserver(sha1($observer), $observers[sha1($observer)]);
        return;
      }
    }

    \Phiber\wire::getInstance()->removeObserver(sha1($observer));

  }
  public static function notify(event $event)
  {
    $observers = \Phiber\wire::getInstance()->getObservers();
    if(count($observers)){
      foreach($observers as $observer){

        if(!is_object($observer['object'])){
          $observer['object'] = new $observer['object'];
        }
        $regEvent = strstr($event->current['event'],'.',true);
        if(isset($observer[$regEvent]) && in_array($event->current['event'], $observer[$regEvent]))
        $observer['object']->update($event);
      }
    }

  }
}

?>