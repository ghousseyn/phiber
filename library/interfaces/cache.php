<?php
namespace Phiber\interfaces;


 interface cache
{
  /**
   * Returns a Cache Item representing the specified key.
   *
   * This method must always return an ItemInterface object, even in case of
   * a cache miss. It MUST NOT return null.
   *
   */
  public function getItem($key);

  /**
   * Returns a traversable set of cache items.
   *
   * @param array $keys
   * An indexed array of keys of items to retrieve.
   * @return array|\Traversable
   * A traversable collection of Cache Items keyed by the cache keys of
   * each item. A Cache item will be returned for each key, even if that
   * key is not found. However, if no keys are specified then an empty
   * traversable MUST be returned instead.
   */
  public function getItems(array $keys = array());

  /**
   * Deletes all items in the pool.
   *
   * @return boolean
   *   True if the pool was successfully cleared. False if there was an error.
   */
  public function clear();

  /**
   * Removes multiple items from the pool.
   */
  public function deleteItems(array $keys);

  /**
   * Persists a cache item immediately.
   */
  public function save($key,$item,$ttl = null);

  /**
   * Sets a cache item to be persisted later.
   */
  public function saveDeferred($key,$item,$ttl = null);

  /**
   * Persists any deferred cache items.
   *
   * @return bool
   *   TRUE if all not-yet-saved items were successfully saved. FALSE otherwise.
   */
  public function commit();

}

?>