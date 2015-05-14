<?php
namespace Phiber\Event;

class eventful
{
    public static function getEvents()
    {
        return array();
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

        if (null === $hash) {
            $hash = self::getHash($observer);
        }

        if (strpos($event, '.') === false) {


            foreach ($event::getEvents() as $event) {

                self::attach($observer, $event, $hash);
            }
            return true;
        }
        $method = 'update';
        if (null !== $m) {
            $method = $m;
        }

        \Phiber\phiber::getInstance()->addObserver($event, array($hash => array('object' => $observer, 'method' => $method)));

        return $hash;

    }

    public static function detach($observer = null, $event, $hash = null)
    {
        if (null === $hash) {
            if (null === $observer) {
                return false;
            }
            $hash = self::getHash($observer);
        }

        if (strpos($event, '.') === false) {
            foreach ($event::getEvents() as $event) {
                self::detach($observer, $event);
            }
            return true;
        }

        \Phiber\phiber::getInstance()->removeObserver($event, $hash);
        return $hash;
    }

    public static function notify(event $event)
    {

        $observers = \Phiber\phiber::getInstance()->getObservers($event->current['event']);

        if (count($observers)) {

            foreach ($observers as $hash => $observer) {

                if (is_string($observer['object'])) {

                    $observer['object'] = new $observer['object'];
                }

                if (is_callable($observer['object'])) {
                    $observer['object']($event);
                } else {
                    $observer['object']->{$observer['method']}($event);
                }

            }
        }

    }
}

?>