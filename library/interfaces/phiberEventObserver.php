<?php
namespace Phiber\Interfaces;

interface phiberEventObserver
{
    /**
     *
     * @param \Phiber\Event\event $event
     */
    public function update(\Phiber\Event\event $event);

    /**
     *
     */
    public function __toString();
}

?>