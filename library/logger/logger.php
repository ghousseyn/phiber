<?php
namespace Phiber\Logger;

abstract class logger extends \Phiber\phiber
{
  const EVENT_ERROR = 'phiber.error';

  const EMERGENCY = 'emergency';
  const ALERT     = 'alert';
  const CRITICAL  = 'critical';
  const ERROR     = 'error';
  const WARNING   = 'warning';
  const NOTICE    = 'notice';
  const INFO      = 'info';
  const DEBUG     = 'debug';

  public $level = 'warning';
  public $prepend;
  public $append;

  protected $sevirity = array(
                              9801 => 'emergency',
                              9802 => 'alert',
                              9803 => 'critical',
                              9804 => 'error',
                              9805 => 'warning',
                              9806 => 'notice',
                              9807 => 'info',
                              9808 => 'debug'
                              );

  public function __construct($params = array()){}

  /**
   * Handle the log request by deciding the severity and if we should log it or not
   * Unknown severities will become 'alerts'
   *@param $exception ErrorException
   *@param $context array
   *@return null
   */
  public function handle(\ErrorException $exception, array $context)
  {
    $levels = array_flip($this->sevirity);
    $sevirity = $exception->getSeverity();

    if(!in_array($sevirity, $levels)){
       $sevirity = 9802;
    }
    $sevirities = array_slice($levels,$levels[$this->level]-9809,null,true);

    if(!array_key_exists($this->sevirity[$sevirity], $sevirities) || array_shift($sevirities) == $levels[$this->sevirity[$sevirity]] ){

      $context['exception'] = $exception;
      $this->{$this->sevirity[$sevirity]}($exception->getMessage(),$context);
    }else{
      return false;
    }

  }
/**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function emergency($message, array $context = array())
  {
    $this->log(self::EMERGENCY, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function alert($message, array $context = array())
  {
    $this->log(self::ALERT, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function critical($message, array $context = array())
  {
    $this->log(self::CRITICAL, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function error($message, array $context = array())
  {
    $this->log(self::ERROR, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function warning($message, array $context = array())
  {
    $this->log(self::WARNING, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function notice($message, array $context = array())
  {
    $this->log(self::NOTICE, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function info($message, array $context = array())
  {
    $this->log(self::INFO, $message, $context);
  }

  /**
   *
   * @param $message string
   * @param $context array
   * @return null
   */
  public function debug($message, array $context = array())
  {
    $this->log(self::DEBUG, $message, $context);
  }
  public static function getEvents()
  {
    return array(self::EVENT_ERROR);
  }
  /**
   * @param $level mixed
   * @param $message string
   * @param $context array
   * @return null
   */
  protected abstract function log($level, $message, array $context = array());
}

?>