<?php
/**
 * Event class.
 * @version    1.0
 * @author     Housseyn Guettaf <ghoucine@gmail.com>
 * @package    Phiber
 */
namespace Phiber\Event;

class event
{
    protected $authority;
    protected $name;
    protected $type;
    protected $payload;
    protected $vars = array();

    public function __construct($eventName, $issuer, $payload = null, $type = 'notification')
    {
        $this->name = $eventName;
        $this->authority = $issuer;
        $this->type = $type;
        $this->payload = $payload;

    }

    /**
     * @return null
     */
    public function getPayload()
    {
        return $this->payload;
    }
    /**
     * @return mixed
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    public function __toString()
    {
        return __CLASS__;
    }

    public function __set($var, $value)
    {
        $this->vars[$var] = $value;
    }

    public function __get($var)
    {
        if (isset($this->vars[$var])) {
            return $this->vars[$var];
        }
    }
}

?>