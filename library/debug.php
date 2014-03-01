<?php

class debug extends Codup\main
{
  public $stack;

  private $timestart;

  protected $queries = array();

  protected $mem = null;

  protected static $dbg = null;

  protected function __construct()
  {
    // xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    $this->mem = memory_get_usage();
    $this->timestart = microtime(true);
  }
  public static function getInstance()
  {
    return new self();
  }

  public function execTime()
  {
    return number_format((microtime(true) - $this->timestart), 4);
  }

  public function memoryUsage()
  {
    return $this->load('tools')->convertSize(memory_get_usage() - $this->mem);
  }

  public  function output()
  {

  }
}

?>
