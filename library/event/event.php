<?php
namespace Phiber\Event;

class event
{
  public $current = null;

  protected $vars = array();

  public function __construct($eventName, $issuer)
  {
    $this->current = array('event' => $eventName, 'from' => $issuer);
  }
  public function __toString()
  {
    return __CLASS__;
  }
  public function __set($var,$value)
  {
    $this->vars[$var] = $value;
  }
  public function __get($var)
  {
    if(isset($this->vars[$var])){
      return $this->vars[$var];
    }
  }
}

?>