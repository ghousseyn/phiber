<?php
namespace Phiber\Interfaces;

interface phiberEventObserver
{
  public function update(\Phiber\Event\event $event);
  public function __toString();
}

?>