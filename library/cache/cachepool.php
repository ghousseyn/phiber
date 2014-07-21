<?php
namespace Phiber\cache;
use Phiber\interfaces\cache;
use Phiber\interfaces\cacheDriver;

class cachePool implements cache
{
  protected $handler;
  protected $queue = array();

  public function __construct(cacheDriver $driver)
  {

    $this->handler = $driver;

  }
  public function getHandle()
  {
    return $this->handler;
  }
  /**
   * Returns a Cache Item representing the specified key.
   */
  public function getItem($key)
  {
    return $this->handler->get($key);
  }

  /**
   * Returns a traversable set of cache items.
   */
  public function getItems(array $keys = array())
  {
    return $this->handler->getMulti($keys);
  }

  /**
   * Deletes all items in the pool.
   *
   * @return boolean
   *   True if the pool was successfully cleared. False if there was an error.
   */
  public function clear()
  {
    return $this->handler->deleteAll();
  }

  /**
   * Removes multiple items from the pool.
   *
   * @param array $keys
   * An array of keys that should be removed from the pool.
   */
  public function deleteItems(array $keys)
  {
    return $this->handler->deleteMulti($keys);
  }

  /**
   * Persists a cache item immediately.
   */
  public function save($key,$item,$ttl = null)
  {
    try{
      $this->handler->set($key,$item,$ttl);
    }catch(\Exception $e){
      return $e->getMessage();
    }
    return true;
  }

  /**
   * Sets a cache item to be persisted later.
   */
  public function saveDeferred($key, $item ,$ttl = null)
  {
    $this->queue[$key]['data'] = $item;
    if(null != $ttl){
      $this->queue[$key]['ttl'] = $ttl;
    }
  }

  /**
   * Persists any deferred cache items.
   *
   * @return bool
   *   TRUE if all not-yet-saved items were successfully saved. FALSE otherwise.
   */
  public function commit()
  {
    foreach($this->queue as $key => $item){
      $ttl = null;
      if(isset($item['ttl'])){
        $ttl = $item['ttl'];
      }
      $this->save($key,$item['data'],$ttl);
    }
  }
  public function __destruct()
  {
    if(count($this->queue)){
      $this->commit();
    }
  }
}

?>