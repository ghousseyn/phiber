<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/wire.php';
require_once 'library/phiber.php';

class phiberTest extends PhiberTests
{
    private $main = null;

    public function setUp()
    {

        $this->main = \Phiber\phiber::getInstance();
        spl_autoload_register(array($this->main, 'autoload'), true, true);
        //$this->setProperty($this->main, 'confFile', 'config.php');
    }


    /**
     *
     * @dataProvider providerURI
     */

    public function testIsValidURI($uri, $out)
    {
        $uri = $this->invokeMethod($this->main, 'uriNormalize', array($uri));
        $this->assertEquals($uri, $out);

    }

    /**
     *
     * @dataProvider providerNotURI
     */

    public function testIsNotValidURI($uri)
    {
        $uri = $this->invokeMethod($this->main, 'uriNormalize', array($uri));
        $return = $this->invokeMethod($this->main, 'isValidURI', array($uri));
        $this->assertFalse($return);
    }


    public function testGet()
    {
        $value = 'test';
        $index = 'index';
        $this->invokeMethod($this->main, 'register', array($index, $value));
        $return = $this->invokeMethod($this->main, 'get', array($index));
        $this->assertEquals($return, $value);
    }

    public function test_requestDefault()
    {
        $this->invokeMethod($this->main, 'register', array('_request', array('var1' => 'val1', 'var2' => 'val2')));
        $return = $this->invokeMethod($this->main, '_requestParam', array('var3', 'val3'));
        $this->assertEquals($return, 'val3');
    }

    public function testAutoloadSimple()
    {
        $class = 'Phiber\\controller';
        $this->invokeMethod($this->main, 'autoload', array($class));
        $this->assertTrue(class_exists($class));
    }

    public function testAutoloadOneLevel()
    {
        $class = 'Phiber\\flag\\flag';
        $this->invokeMethod($this->main, 'autoload', array($class));
        $this->assertTrue(class_exists($class));
    }

    public function testHasActionDefault()
    {
        $controller = 'index';
        $parts = array('nonExistant');
        $return = $this->invokeMethod($this->main, 'hasAction', array(&$parts, $controller));
        $this->assertEquals(0, count($parts));
        $this->assertEquals('action' . ucfirst(Phiber\config::getInstance()->PHIBER_CONTROLLER_DEFAULT_METHOD), $return);
    }

    public function testHasAction()
    {
        $controller = 'index';
        $parts = array('main');
        $return = $this->invokeMethod($this->main, 'hasAction', array(&$parts, $controller));
        $this->assertEquals(count($parts), 0);
        $this->assertEquals('actionMain', $return);
    }

    public function testSetVars()
    {
        $expected = array('var1' => 'val1', 'var2' => 'val2');
        $parts = array('var1', 'val1', 'var2', 'val2');
        $this->invokeMethod($this->main, 'setVars', array($parts));
        $this->assertEquals($expected, $this->getProperty($this->main, '_requestVars'));
    }

    public function testAddRoute()
    {
        $route = array('/route' => 'to/something');
        $this->invokeMethod($this->main, 'addRoute', array('/route', 'to/something'));
        $routes = $this->getProperty($this->main, 'routes');
        $this->assertEquals(1, count($routes));
    }

    public function providerURI()
    {
        return array(
            //URI validation and transformations
            array('/module/controller/action/?val=var', '/module/controller/action/val/var'),
            array('/module/controller/action?val=var', '/module/controller/action/val/var'),
            array('/module/controller/action/val=var&var2=val2', '/module/controller/action/val/var/var2/val2'),
            array('/module/controller/action/val/var/var2/val2', '/module/controller/action/val/var/var2/val2'),
            array("/module/controller/action/var/value -_,$.'!()*", "/module/controller/action/var/value -_,$.'!()*"),
        );
    }

    public function providerNotURI()
    {
        return array(
            array('/module/controller/action?val=var%00"'),
            array('/module/controller/action/val=var&var2=val2<'),
            array('/module/controller/action/val/var/var2/val2>'),
            array('/module/controller/action/val=var&var2=val2#'),
            array("/module/controller/action/var/value{"),
            array("/module/controller/action/var/value}"),
            array("/module/controller/action/var/value|"),
            array("/module/controller/action/var/value^"),
            array("/module/controller/action/var/value`"),
        );
    }
}
