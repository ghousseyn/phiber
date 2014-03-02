<?php

namespace Tests;
require_once 'library/main.php';
use Codup;
class mainTest extends \PHPUnit_Framework_TestCase
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
      $reflection = new \ReflectionClass(get_class($object));
      $method = $reflection->getMethod($methodName);
      $method->setAccessible(true);

      return $method->invokeArgs($object, $parameters);
    }

    public function providerURI()
    {
      return array(
        array('/module/controller/action/?val=var', '/module/controller/action/val/var'),
        array('/module/controller/action?val=var', '/module/controller/action/val/var'),
        array('/module/controller/action/val=var&var2=val2', '/module/controller/action/val/var/var2/val2'),
        array('/module/controller/action/val/var/var2/val2', '/module/controller/action/val/var/var2/val2'),
      );
    }

     /**
    * @dataProvider providerURI
    */


    public function testIsValidURI($uri,$out)
    {
      Codup\main::getInstance()->isValidURI($uri);
      $this->assertEquals($uri,$out);
    }
}
