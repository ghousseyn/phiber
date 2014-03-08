<?php

class error
{

  public $debug = false;

  public static $instance= null;

  private $writer = null;

  protected function __construct()
  {
    set_error_handler('error::error_handler');
    set_exception_handler('error::exception_handler');

    if(! ini_get('log_errors')){
      ini_set('log_errors', true);
    }
  }
  public static function initiate(logger\logger $writer)
  {
    self::$instance = new self();
    self::$instance->setWriter($writer);
    return self::$instance;
  }

  public function setWriter(logger\logger $writer)
  {
    $this->writer = $writer;
  }

  public function write(ErrorException $exception, $context = array())
  {
    $this->writer->handle($exception, $context);
  }

  public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
  {
    $l = error_reporting();
    if($l & $errno){

      $exit = false;
      switch($errno){
        case E_USER_ERROR:
          $type = 'Fatal Error';
          $sevirity = 9801;//emergency
          $exit = true;
          break;
        case E_USER_WARNING:
          $type = 'Warning';
          $sevirity = 9805;//warning
          break;
        case E_WARNING:
          $type = 'Warning';
          $sevirity = 9803;//critical
          break;
        case E_USER_NOTICE:
        case E_NOTICE:
        case @E_STRICT:
          $type = 'Notice';
          $sevirity = 9806;//notice
          break;
        case @E_RECOVERABLE_ERROR:
          $type = 'Catchable';
          $sevirity = 9804;//error
          break;
        default:
          $type = 'Unknown Error';
          $sevirity = 9802;//alert
          $exit = true;
          break;
      }

      $exception = new \ErrorException($type . ': ' . $errstr, $errno, $sevirity, $errfile, $errline);

      self::exception_handler($exception,$errcontext);

      if($exit){
        exit();
      }
    }
    return false;
  }

  public static function exception_handler($exception,$context=array())
  {

    if($exception instanceof \ErrorException){
      self::$instance->write($exception,$context);
    }elseif($exception instanceof \Exception){
      self::$instance->write(new \ErrorException($exception->getMessage(), 0,$exception->getCode(),$exception->getFile(),$exception->getLine()),$context);
    }

  }

}

?>