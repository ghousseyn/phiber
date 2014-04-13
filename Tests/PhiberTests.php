<?php


class PhiberTests extends \PHPUnit_Framework_TestCase
{
  /**
   * Call protected/private method of a class.
   *
   * @param object &$object    Instantiated object that we will run method on.
   * @param string $methodName Method name to call
   * @param array  $parameters Array of parameters to pass into method.
   *
   * @return mixed Method return.
   */

  public function invokeMethod(&$object, $methodName, array $parameters = array())
  {
    $reflection = new \ReflectionClass($object);
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }

  /**
   * Get value of a protected/private property of a class.
   *
   * @param object &$object    Instantiated object that we will run method on.
   * @param string $propertyName Method name to call
   *
   * @return mixed Property value.
   */

  public function getProperty(&$object, $propertyName)
  {
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($propertyName);
    $property->setAccessible(true);

    return $property->getValue($object);
  }

  /**
   * Set value of a protected/private property of a class.
   *
   * @param object &$object Instantiated object that we will run method on.
   * @param string $propertyName Method name to call
   * @param mixed Property value
   */

  public function setProperty(&$object, $propertyName,$value)
  {
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($propertyName);
    $property->setAccessible(true);

    return $property->setValue($object,$value);
  }

  public function __autoload($class)
  {

    $parts = explode('\\', $class);


    $count = count($parts);
    if($parts[0] == 'Phiber'){

      $path = './library/';
    }
    for($i=0; $i < $count; $i++){
      if($parts[$i] === 'Phiber'){
        continue;
      }
      if($i == $count - 1){
        $path .= $parts[$i] . '.php';
        break;
      }
      $path .=  strtolower($parts[$i]) . '/';

    }

    if(file_exists($path)){
      include_once $path;
      return;
    }

  }

}

?>