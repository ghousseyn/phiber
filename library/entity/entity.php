<?php
namespace Phiber\entity;

use Phiber\oosql\oosql;

abstract class entity
{

  protected static $oosql_model_extra = false;

  abstract public function getRelations();

  /*
   * Deliver an oosql instance instead and pass this class name so we can get
   * back with the caller instance with results
   */
  public static function getInstance()
  {

    return self::getooSQL(get_class(new static()));
  }

  protected static function getooSQL($class)
  {

    $tablename = strstr($class, '\\');
    if($tablename === false){
      return false;
    }

    $tablename = trim(str_replace('\\', '', $tablename));
    return oosql::getInstance($tablename, $class);
  }

  public function save($obj)
  {

    if(self::$oosql_model_extra){

      $originalProps = get_object_vars(new static);
      $mixedProps = array_keys(get_object_vars($obj));

      foreach($mixedProps as $property){
        if(! key_exists($property, $originalProps)){
          unset($obj->{$property});
        }
      }
      // self::$oosql_model_extra = false;
    }

    $oosql = self::getooSQL(get_class($obj));
    $oosql->save($obj);

  }

  public function load($obj)
  {

    $primary = $obj->getPrimary();
    if(isset($obj->{$primary[0]})){
      return self::getooSQL(get_class($obj))->findOne($obj->getPrimaryValue())->fetch()->object();
    }
  }

  public function __set($var, $val)
  {
    if(! key_exists($var, get_object_vars(new static))){
      self::$oosql_model_extra = true;
    }
    $this->{$var} = $val;
  }

  public function __unset($property)
  {
    unset($this->properties[$property]);
  }

  public function __call($tablename, $arg)
  {
    $relations = $this->getRelations();
    $obj = $this;
    foreach($relations as $fk => $target){
      $objPath = explode('.', $target);
      if($objPath[0] == $tablename){
        $tablename = "models\\$tablename";
        $instance = $tablename::getInstance()->getModelObject();
        $instance->{$objPath[1]} = $obj->{$objPath[1]};

        return $instance;
      }

    }
  }

  public function __get($var)
  {
    if(key_exists($var, get_object_vars(new static))){
      return $this->{$var}();
    }

  }
}
?>