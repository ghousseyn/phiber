<?php
/**
 *
 * @covers main
 *
 */
namespace Tests;
require_once 'library/main.php';
use Codup;
class mainTest extends \PHPUnit_Framework_TestCase
{
    private $main = null;

    public function setUp(){
      $this->main = Codup\main::getInstance();
    }




    public function testGetView(){

      $route = array('module' => 'default', 'controller' => 'index', 'action' => 'main');
      $this->invokeMethod($this->main,'register',array('route',$route));
      $this->invokeMethod($this->main,'getView');
      $this->assertFileExists($this->main->view->viewPath);
    }

    public function testViewFileNotAvailable(){

      $route = array('module' => 'defaulte', 'controller' => 'indexe', 'action' => 'mainee');
      $this->invokeMethod($this->main,'register',array('route',$route));
      $this->invokeMethod($this->main,'getView');
      $this->assertFileNotExists($this->main->view->viewPath);
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

    public function testSetVars(){
      $expected = array('var1'=>'val1','var2'=>'val2');
      $parts = array('var1', 'val1', 'var2', 'val2');
      $this->invokeMethod($this->main,'setVars',array($parts));
      $this->assertEquals($this->getProperty($this->main,'_requestVars'), $expected);
    }

    public function testContextSwitchJson()
    {
      $this->invokeMethod($this->main,'contextSwitch',array('json'));
      $this->assertEquals($this->invokeMethod($this->main,'get',array('context')),'json');
    }
    public function testContextSwitchHtml()
    {
      $this->invokeMethod($this->main,'contextSwitch',array('html'));
      $this->assertEquals($this->invokeMethod($this->main,'get',array('context')),'html');
    }

    public function testIsPost()
    {
      $this->invokeMethod($this->main,'register',array('post',true));
      $this->assertTrue($this->invokeMethod($this->main,'isPost'));
    }

    public function testIsGet()
    {
      $this->invokeMethod($this->main,'register',array('get',true));
      $this->assertTrue($this->invokeMethod($this->main,'isGet'));
    }

    public function testIsAjax()
    {
      $this->invokeMethod($this->main,'register',array('ajax',true));
      $this->assertTrue($this->invokeMethod($this->main,'isAjax'));
    }
    public function testIsNotPost()
    {
      $this->invokeMethod($this->main,'register',array('post',false));
      $this->assertFalse($this->invokeMethod($this->main,'isPost'));
    }

    public function testIsNotGet()
    {
      $this->invokeMethod($this->main,'register',array('get',false));
      $this->assertFalse($this->invokeMethod($this->main,'isGet'));
    }

    public function testIsNotAjax()
    {
      $this->invokeMethod($this->main,'register',array('ajax',false));
      $this->assertFalse($this->invokeMethod($this->main,'isAjax'));
    }

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
      $reflection = new \ReflectionClass(get_class($object));
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
      $reflection = new \ReflectionClass(get_class($object));
      $property = $reflection->getProperty($propertyName);
      $property->setAccessible(true);

      return $property->setValue($object,$value);
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
