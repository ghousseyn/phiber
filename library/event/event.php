<?php
namespace Phiber\Event;

class event
{
  public $current = null;

  public function __construct($eventName, $issuer)
  {
    $this->current = array('event' => $eventName, 'from' => $issuer);
  }
  public function __toString()
  {
    return __CLASS__;
  }
}

?>