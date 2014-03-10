<?php

class debug extends Phiber\main
{
  public $stack;

  private $timestart;

  protected $queries = array();

  protected $mem = null;

  protected static $dbg = null;

  protected function __construct()
  {

  }
  public static function getInstance()
  {
    return new self();
  }

  public  function output()
  {

  }
}

?>
