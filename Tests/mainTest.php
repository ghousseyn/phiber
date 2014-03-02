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
        //URI validation and transformations
        array('/module/controller/action/?val=var', '/module/controller/action/val/var'),
        array('/module/controller/action?val=var', '/module/controller/action/val/var'),
        array('/module/controller/action/val=var&var2=val2', '/module/controller/action/val/var/var2/val2'),
        array('/module/controller/action/val/var/var2/val2', '/module/controller/action/val/var/var2/val2'),
        //special characters allowed: space - _ , $ . ' ! ( )*
        array("/module/controller/action/var/value -_,$.'!()*", "/module/controller/action/var/value -_,$.'!()*"),
      );
    }

    public function providerNotURI()
    {
      return array(
          //special characters not allowed: ; [ ] " < > # % { } | \ ^ ~ [ ] `
          array('/module/controller/action/?val=var\0'),
          array('/module/controller/action?val=var"'),
          array('/module/controller/action/val=var&var2=val2<'),
          array('/module/controller/action/val/var/var2/val2>'),
          array("/module/controller/action/var/value;"),
          array('/module/controller/action/?val=var['),
          array('/module/controller/action?val=var]'),
          array('/module/controller/action/val=var&var2=val2#'),
          array('/module/controller/action/val/var/var2/val2%'),
          array("/module/controller/action/var/value{"),
          array("/module/controller/action/var/value}"),
          array("/module/controller/action/var/value|"),
          array("/module/controller/action/var/value\\"),
          array("/module/controller/action/var/value^"),
          array("/module/controller/action/var/value`"),
      );
    }
     /**
    * @dataProvider providerURI
    */


    public function testIsValidURI($uri,$out)
    {
      $main =  Codup\main::getInstance();
      $this->invokeMethod($main,'isValidURI',array(&$uri));
      $this->assertEquals($uri,$out);
    }
    /**
     * @dataProvider providerNotURI
     */
    public function testIsNotValidURI($uri)
    {
      $main =  Codup\main::getInstance();
      $return = $this->invokeMethod($main,'isValidURI',array(&$uri));
      $this->assertFalse($return);
    }
}
