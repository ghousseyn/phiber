<?php
namespace Phiber;

class error
{

  private static $stop = false;

  public static $instance;

  private $writer = null;

  protected function __construct()
  {
    set_error_handler('Phiber\error::error_handler');
    set_exception_handler('Phiber\error::exception_handler');
    register_shutdown_function('Phiber\error::fatal_error_handler');

    error_reporting(E_ALL);
    ini_set('display_errors', false);
    if(! ini_get('log_errors') && \config::getInstance()->PHIBER_LOG){
      ini_set('log_errors', true);
    }
  }
  public static function initiate(Logger\logger $writer)
  {
    self::$instance = new self();
    self::$instance->setWriter($writer);
    return self::$instance;
  }

  public function setWriter(Logger\logger $writer)
  {
    $this->writer = $writer;
  }

  public function write(\ErrorException $exception, $context = array())
  {
    $this->writer->handle($exception, $context);
  }

  public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
  {

    $l = error_reporting();
    if($l & $errno){
      restore_error_handler();
      self::$stop = false;
      switch($errno){
        case E_ERROR:
        case E_COMPILE_ERROR:
        case E_CORE_ERROR:
        case E_PARSE:
        case E_USER_ERROR:
          $type = 'Fatal Error';
          $sevirity = 9801;//emergency
          self::$stop = true;
          break;
        case E_USER_WARNING:
          $type = 'User Warning';
          $sevirity = 9805;//warning
          self::$stop = \config::getInstance()->STOP_ON_USER_WARNINGS;
          break;
        case E_COMPILE_WARNING:
        case E_CORE_WARNING:
        case E_WARNING:
          $type = 'Warning';
          $sevirity = 9803;//critical
          self::$stop = \config::getInstance()->STOP_ON_WARNINGS;
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
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
          $type = 'Depricated';
          $sevirity = 9807;//info
          break;
        default:
          $type = 'Unknown Error';
          $sevirity = 9802;//alert
          //$stop = true;
          break;
      }


      $exception = new \ErrorException($type . ': ' . $errstr, $errno, $sevirity, $errfile, $errline);

      self::exception_handler($exception,$errcontext);

      return true;
    }
    return false;
  }

  public static function exception_handler($exception,$context=array())
  {
    if(self::$stop){
       throw $exception;
    }
    restore_exception_handler();
    if($exception instanceof \ErrorException){

      self::$instance->write($exception,$context);
    }elseif($exception instanceof \Exception){

      self::$instance->write(new \ErrorException($exception->getMessage(), 0,$exception->getCode(),$exception->getFile(),$exception->getLine()),$context);
    }

    return true;

  }

  public static function fatal_error_handler()
  {
    $error = error_get_last();
    if(isset($error['type']) ){
      self::error_handler($error['type'], $error['message'], $error['file'], $error['line'], array());
    }
  }

}

?>