<?php
require_once 'Tests/PhiberTests.php';


class sessionTest extends PhiberTests
{
  protected $session;

  public function setUp()
  {

    $this->session = new Phiber\Session\session;


  }
  public function testSessionIsStarted()
  {
    $_SESSION['user'] = 'guest';
    $this->assertTrue($this->session->isStarted());
  }
  public function testSessionVar()
  {
    $var = 'var';
    $value = 'value';

    $this->assertFalse($this->session->exists($var));

    $this->session->set($var, $value);
    $this->assertTrue($this->session->exists($var));

    $this->session->delete($var);
    $this->assertFalse($this->session->exists($var));
  }
  public function testSessionNamespace()
  {
    Phiber\Session\session::$namespace = 'phiberTest';
    $this->assertEquals('phiberTest', $this->session->getNS());
  }

}