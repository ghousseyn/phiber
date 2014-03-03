<?php
/**
 *
 * @covers main
 *
 */
namespace Tests;
require_once 'Tests/coduptests.php';
require_once 'library/main.php';
use Codup;
class mainTest extends CodupTests
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
      $class = 'Codup\\controller';
      $return = $this->invokeMethod($this->main,'load',array($class));
      $this->assertFalse($return);
    }
    public function testAutoloadSimple()
    {
      $class = 'Codup\\controller';
      $this->invokeMethod($this->main,'autoload',array($class));
      $this->assertTrue(class_exists($class));
    }

    public function testAutoloadOneLevel()
    {
      $class = 'oosql\\oosql';
      $this->invokeMethod($this->main,'autoload',array($class));
      $this->assertTrue(class_exists($class));
    }

    public function testAutoloadOneLevelGlobalShifted()
    {
      $class = 'Codup\\oosql\\oosql';
      $this->invokeMethod($this->main,'autoload',array($class));
      $this->assertTrue(class_exists('oosql\\oosql'));
    }

    public function testGet()
    {
      $value = 'test';
      $index = 'index';
      $this->invokeMethod($this->main,'register',array($index,$value));
      $return = $this->invokeMethod($this->main,'get',array($index));
      $this->assertEquals($return, $value);
    }

    public function testHasActionDefault()
    {
      $controller = 'index';
      $parts = array('nonExistant');
      $return = $this->invokeMethod($this->main,'hasAction',array(&$parts,$controller));
      $this->assertEquals(count($parts),0);
      $this->assertEquals($return, $this->main->conf->defaultMethod);
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
      $this->assertEquals(count($parts),1);
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

    public function test_request()
    {
      $this->invokeMethod($this->main,'register',array('_request',array('var1'=>'val1','var2'=>'val2')));
      $return = $this->invokeMethod($this->main,'_request',array('var1'));
      $this->assertEquals($return, 'val1');
    }
    public function test_requestDefault()
    {
      $this->invokeMethod($this->main,'register',array('_request',array('var1'=>'val1','var2'=>'val2')));
      $return = $this->invokeMethod($this->main,'_request',array('var3','val3'));
      $this->assertEquals($return, 'val3');
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
