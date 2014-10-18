<?php
namespace Phiber\Interfaces;

interface phiberEventSubject
{
    /**
     *
     */
    public static function getEvents();

    /**
     *
     * @param \Phiber\Event\event $event
     */
    public static function notify(\Phiber\Event\event $event);

    /**
     *
     * @param \Phiber\Interfaces\phiberEventObserver $observer
     */
    public static function attach($observer);

    /**
     *
     * @param \Phiber\Interfaces\phiberEventObserver $observer
     */
    public static function detach($observer);

}

?>