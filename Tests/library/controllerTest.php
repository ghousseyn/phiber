<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/main.php';
require_once 'library/controller.php';


class controllerTest extends PhiberTests
{

  private $controller = null;

  public function setUp()
  {
    $this->controller = Phiber\controller::getInstance();
  }

  /**
   * @runInSeparateProcess
   */
  public function testSendJSON()
  {
    $array = array('test','key' => 'value', 7 => 'another value', 12 => array('test2' => 'value two'));
    $json = $this->invokeMethod($this->controller,'sendJSON',array($array));
    $headers = xdebug_get_headers();
    $this->assertNotEmpty($headers);
    $this->assertContains('Content-type: application/json; charset=utf-8', $headers);
    $this->assertTrue($json);
  }
  public function testDisableLayout()
  {
    $this->invokeMethod($this->controller,'disableLayout');
    $return = $this->invokeMethod($this->controller,'get',array('layoutEnabled'));
    $this->assertFalse($return);
  }

}

?>