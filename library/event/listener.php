<?php
namespace Phiber\Event;

use Phiber\Interfaces\phiberEventObserver;
use Phiber\Event\event;

abstract class listener implements phiberEventObserver
{
  abstract public function update(event $event);
  public function __tostring()
  {
    $reflect = new \ReflectionClass(get_called_class());
    return $reflect->getFileName();
  }
}

?>