<?php
namespace Phiber\Interfaces;

interface phiberEventSubject
{
  public static function notify(\Phiber\Event\event $event);
  public static function attach(phiberEventObserver $observer);
  public static function detach(phiberEventObserver $observer);

}

?>