<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/phiber.php';

class phiberTest extends PhiberTests
{
    private $main = null;

    public function setUp(){

      $this->main = \Phiber\phiber::getInstance();
    }


    /**
    *
    * @dataProvider providerURI
    */

    public function testIsValidURI($uri,$out)
    {
      $this->invokeMethod($this->main,'isValidURI',array(&$uri));
      $this->assertEquals($uri,$out);

    }

    /**
     *
     * @dataProvider providerNotURI
     */

    public function testIsNotValidURI($uri)
    {
      $return = $this->invokeMethod($this->main,'isValidURI',array(&$uri));
      $this->assertFalse($return);
    }

    public function testLoad()
    {
      $class = 'config';
      $return = $this->invokeMethod($this->main,'load',array($class));
      $this->assertInstanceOf($class, $return);
    }

    public function testLoadReturnClassNameAfterInclusion()
    {
      $class = 'config';
      $return = $this->invokeMethod($this->main,'load',array($class,null,null,false));
      $this->assertTrue(class_exists($return));
    }

    public function testLoadFalse(){
      $class = 'Phiber\\controller';
      $return = $this->invokeMethod($this->main,'load',array($class));
      $this->assertFalse($return);
    }
    public function testAutoloadSimple()
    {
      $class = 'Phiber\\controller';
      $this->invokeMethod($this->main,'autoload',array($class));
      $this->assertTrue(class_exists($class));
    }

    public function testAutoloadOneLevel()
    {
      $class = 'Phiber\\oosql\\oosql';
      $this->invokeMethod($this->main,'autoload',array($class));
      $this->assertTrue(class_exists($class));
    }

    public function testAutoloadOneLevelGlobalShifted()
    {
      $class = 'Phiber\\oosql\\oosql';
      $this->invokeMethod($this->main,'autoload',array($class));
      $this->assertTrue(class_exists('Phiber\\oosql\\oosql'));
    }

    public function testHasActionDefault()
    {
      $controller = 'index';
      $parts = array('nonExistant');
      $return = $this->invokeMethod($this->main,'hasAction',array(&$parts,$controller));
      $this->assertEquals(count($parts),0);
      $this->assertEquals($return, \config::getInstance()->PHIBER_CONTROLLER_DEFAULT_METHOD);
    }

    public function testHasAction()
    {
      $controller = 'index';
      $parts = array('main');
      $return = $this->invokeMethod($this->main,'hasAction',array(&$parts,$controller));
      $this->assertEquals(count($parts),0);
      $this->assertEquals($return, 'main');
    }

    public function testHasController()
    {
      $module = 'default';
      $parts = array('index','main');
      $return = $this->invokeMethod($this->main,'hasController',array(&$parts,$module));

      $this->assertEquals($return, 'index');
    }

    public function testHasControllerDefault()
    {
      $module = 'default';
      $parts = array('nonExistant','main');
      $return = $this->invokeMethod($this->main,'hasController',array(&$parts,$module));
      $this->assertEquals(count($parts),2);
      $this->assertEquals($return, 'index');
    }

    public function testSetVars()
    {
      $expected = array('var1'=>'val1','var2'=>'val2');
      $parts = array('var1', 'val1', 'var2', 'val2');
      $this->invokeMethod($this->main,'setVars',array($parts));
      $this->assertEquals($this->getProperty($this->main,'_requestVars'), $expected);
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
}
