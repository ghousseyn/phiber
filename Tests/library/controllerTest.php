<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/phiber.php';
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
    $array = array('test', 'value', 'another value', 'test2' );
    $json = $this->invokeMethod($this->controller,'sendJSON',array($array));
    $headers = xdebug_get_headers();
    $this->assertNotEmpty($headers);
    $this->assertContains('Content-type: application/json; charset=utf-8', $headers);
    $this->assertTrue($json);
  }

}

?>