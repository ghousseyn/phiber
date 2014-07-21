<?php
namespace Phiber\cache;

class item
{
  public $data,
         $ttl,
         $timestamp;

  public function setTtl($ttl)
  {
    $this->ttl = (int) $ttl;
    $this->timestamp = microtime(true);
  }

}

?>