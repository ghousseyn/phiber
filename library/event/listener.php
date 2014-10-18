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

}

?>