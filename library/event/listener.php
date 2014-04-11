<?php
namespace Phiber\Event;

use Phiber\Interfaces\phiberEventObserver;
use Phiber\Event\event;

abstract class listener implements phiberEventObserver
{
  /**
   * (non-PHPdoc)
   * @see Phiber\Interfaces.phiberEventObserver::update()
   */
  abstract public function update(event $event);
  /**
   * Returns a string with the class name and the file path to be used to generate a unique hash
   * @return string The classname.filepath string
   */
  public function __tostring()
  {
    $class = get_called_class();
    $reflect = new \ReflectionClass($class);
    return $class.$reflect->getFileName();
  }
}

?>