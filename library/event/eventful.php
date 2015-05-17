<?php
namespace Phiber\Event;

class eventful
{
    const EVENT_NOTOBJECT = 'eventful.notobject';

    public static function getEvents()
    {
        return array(self::EVENT_NOTOBJECT);
    }

    public static function getHash($observer)
    {
        if (is_callable($observer)) {
            return sha1(spl_object_hash($observer));
        }
        return sha1($observer);
    }

    public static function attach($observer, $event, $hash = null, $m = null)
    {
        if (!is_object($observer)) {
            self::notify(new event(self::EVENT_NOTOBJECT, 'eventful', 'Attach: Observer is not an object!', 'error'));
            return false;
        }
        if (null === $hash) {
            $hash = self::getHash($observer);
        }
        //Check if we have the full event name
        if (strpos($event, '.') === false) {
            //Get all the events exposed by that class
            foreach ($event::getEvents() as $newEvent) {
                self::attach($observer, $newEvent, $hash, $m);
            }
            return $hash;
        }
        //What method should we run on event
        $method = 'update';
        if (null !== $m) {
            $method = $m;
        }
        $observerMeta = array('object' => $observer, 'method' => $method);

        $currentEvents = \Phiber\phiber::getInstance()->getCurrentEvents();
        //was the event we're registering for already fired
        if (array_key_exists($event, $currentEvents)) {
            //Let's pass all events fired to our object
            foreach ($currentEvents[$event] as $eventObject ){
                self::runNotification($observerMeta, $eventObject);
            }
        }
        //Register our observer
        \Phiber\phiber::getInstance()->addObserver($event, array($hash => $observerMeta));

        return $hash;

    }

    public static function detach($observer = null, $event, $hash = null)
    {
        if (null !== $observer && !is_object($observer)) {
            self::notify(new event(self::EVENT_NOTOBJECT, 'eventful', 'Detach: Observer is not an object!', 'error'));
            return false;
        }
        if (null === $hash) {
            $hash = self::getHash($observer);
        }

        if (strpos($event, '.') === false) {
            foreach ($event::getEvents() as $event) {
                self::detach($observer, $event, $hash);
            }
            return true;
        }

        return \Phiber\phiber::getInstance()->removeObserver($event, $hash);
    }

    public static function notify(event $event)
    {
        \Phiber\phiber::getInstance()->pushEvent($event);

        $observers = \Phiber\phiber::getInstance()->getObservers($event->getName());

        if (count($observers)) {

            foreach ($observers as $hash => $observer) {

                if (is_string($observer['object'])) {

                    $observer['object'] = new $observer['object'];
                }

               self::runNotification($observer, $event);

            }
        }

    }
    protected static function runNotification($observer, $event)
    {
        if (is_callable($observer['object'])) {
            $observer['object']($event);
        } else {
            $observer['object']->{$observer['method']}($event);
        }
    }
}

?>