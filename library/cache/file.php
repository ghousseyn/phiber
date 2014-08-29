<?php
namespace Phiber\Cache;
use Phiber\Interfaces\cacheDriver;

class file implements cacheDriver
{
  protected $ttl = 86400;
  protected $key;
  protected $path;
  protected $exists = false;
  protected $hit = false;
  protected $bag;
  protected $set = false;

  public function __construct($path = null)
  {
    if(null != $path){
      $this->path = $path;
    }else{
      $this->path = \Phiber\config::getInstance()->application.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
    }

  }

  public function set($key,$value,$ttl = null)
  {
    if(null !== $ttl){
      $this->ttl = $ttl;
    }
    if($value instanceof item){
      $this->bag = $value;
    }else{
    $this->bag = new item();
    $this->bag->data = $value;
    $this->bag->setTtl($this->ttl);
    }
    $this->set = true;
    $data = serialize($this->bag);
    $key = sha1($key);
    $return = file_put_contents($this->path.$key, $data, LOCK_EX);
    if($return === false){
      throw new \Exception("Could not create cache file.",9910);
    }
  }
  public function get($key)
  {
    $filename = $this->path.sha1($key);
    if(file_exists($filename)){
      $bag = unserialize(file_get_contents($filename));
      $current = microtime(true);
      if($bag instanceof item ){
        if(($current - $bag->timestamp) > $bag->ttl){
          unlink($filename);
        }else{
          $this->hit = true;
          $this->bag = $bag;
          $this->set = true;
          $this->ttl = $this->bag->ttl;
          return $bag->data;
        }
      }
    }
    return new item;
  }
  public function getMulti($keys)
  {
    foreach($keys as $key){
      $items[$key] = $this->get($key);
    }
    return $items;
  }
  public function deleteMulti($keys)
  {
    foreach($keys as $key){
      $return[] = $this->delete($key);
    }
    if(in_array(false, $return)){
      return false;
    }
    return true;
  }
  public function deleteAll()
  {
    foreach(scandir($this->path) as $item){
      $return[] = $this->delete($item);
    }
    if(in_array(false, $return)){
      return false;
    }
    return true;
  }
  public function delete($key)
  {
    $path = $this->path.$key;
    if(stream_resolve_include_path($path)){
      return unlink($path);
    }else{
      return false;
    }
  }
  public function getBag()
  {
    return $this->bag;
  }
  public function getKey()
  {
    return $this->key;
  }
  public function isHit()
  {
    return $this->hit;
  }
  public function exists($key)
  {
    $filename = $this->path.sha1($key);
    if(stream_resolve_include_path($filename)){
      return true;
    }
    return false;
  }
  public function setExpiration($ttl = null)
  {
    $this->ttl = $ttl;
  }
  public function getExpiration()
  {
    return $this->ttl;
  }
}

?>