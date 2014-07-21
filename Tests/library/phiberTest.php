<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/phiber.php';

class phiberTest extends PhiberTests
{
    private $main = null;

    public function setUp(){

      $this->main = \Phiber\phiber::getInstance();
      spl_autoload_register(array($this->main, 'autoload'),true,true);
      $this->setProperty($this->main, 'confFile', 'config.php');
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

    public function testGetView(){

      $route = array('module' => 'default', 'controller' => 'index', 'action' => 'main');
      $this->invokeMethod($this->main,'register',array('route',$route));
      $this->invokeMethod($this->main,'getView');
      $this->assertEquals('application'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'index'.DIRECTORY_SEPARATOR.'main.php',$this->main->view->viewPath);
    }
    public function testGet()
    {
      $value = 'test';
      $index = 'index';
      $this->invokeMethod($this->main,'register',array($index,$value));
      $return = $this->invokeMethod($this->main,'get',array($index));
      $this->assertEquals($return, $value);
    }
    public function test_request()
    {
      $this->invokeMethod($this->main,'register',array('_request',array('var1'=>'val1','var2'=>'val2')));
      $return = $this->invokeMethod($this->main,'_requestParam',array('var1'));
      $this->assertEquals($return, 'val1');
    }
    public function test_requestDefault()
    {
      $this->invokeMethod($this->main,'register',array('_request',array('var1'=>'val1','var2'=>'val2')));
      $return = $this->invokeMethod($this->main,'_requestParam',array('var3','val3'));
      $this->assertEquals($return, 'val3');
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
      $this->assertEquals(0,count($parts));
      $this->assertEquals(\config::getInstance()->PHIBER_CONTROLLER_DEFAULT_METHOD,$return);
    }

    public function testHasAction()
    {
      $controller = 'index';
      $parts = array('main');
      $return = $this->invokeMethod($this->main,'hasAction',array(&$parts,$controller));
      $this->assertEquals(count($parts),0);
      $this->assertEquals('main',$return);
    }

    public function testHasController()
    {
      $module = 'default';
      $parts = array('index','main');
      $return = $this->invokeMethod($this->main,'hasController',array(&$parts,$module));

      $this->assertEquals('index',$return);
    }

    public function testHasControllerDefault()
    {
      $module = 'default';
      $parts = array('nonExistant','main');
      $return = $this->invokeMethod($this->main,'hasController',array(&$parts,$module));
      $this->assertEquals(2,count($parts));
      $this->assertEquals('index',$return);
    }

    public function testSetVars()
    {
      $expected = array('var1'=>'val1','var2'=>'val2');
      $parts = array('var1', 'val1', 'var2', 'val2');
      $this->invokeMethod($this->main,'setVars',array($parts));
      $this->assertEquals($expected,$this->getProperty($this->main,'_requestVars'));
    }

   public function testAddRoute()
   {
     $route = array('/route'=>'to/something');
     $this->invokeMethod($this->main,'addRoute',array($route));
     $routes = $this->getProperty($this->main,'routes');
     $this->assertEquals(1,count($routes));
   }
   public function testRouteMatchSimple()
   {
    $routes = array('/info'=>'/default/index/main');
    $this->setProperty($this->main, 'phiber_bootstrap', \bootstrap::getInstance(\config::getInstance()));
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $ret = $this->invokeMethod($this->main, 'routeMatchSimple',array($routes,'/info'));
    $this->assertTrue($ret);
    $this->assertEquals('/default/index/main',$_SERVER['REQUEST_URI']);
   }
   public function testRouteMatchSimpleArray()
   {
     $myroute = array('module'=>'default','controller'=>'index','action'=>'main','vars'=>array('id'=>1));
     $route = array('/info'=>$myroute);
     $this->main->addRoute($route);
     $_SERVER['REQUEST_METHOD'] = 'GET';
     $_SERVER['REQUEST_URI'] = '/info';
     $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
     $this->main->run();
     $route = $this->getProperty($this->main,'route');

     $this->assertEquals($myroute,$route);
   }
   public function testRouteMatchRegex()
   {
     $routes = array('~/info/(\d+)/(\d+)~'=>'/default/index/main/:cat/:id');
     $this->setProperty($this->main, 'phiber_bootstrap', \bootstrap::getInstance(\config::getInstance()));
     $_SERVER['REQUEST_METHOD'] = 'GET';
     $ret = $this->invokeMethod($this->main, 'routeMatchRegex',array($routes,'/info/14/13'));
     $this->assertTrue($ret);
     $this->assertEquals('/default/index/main/cat/14/id/13',$_SERVER['REQUEST_URI']);
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
          //special characters not allowed: ; [ ] " < > # % { } | \ ^ ~ `
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
