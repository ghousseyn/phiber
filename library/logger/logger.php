<?php
namespace logger;

abstract class logger extends \phiber\main
{
  const EMERGENCY = 'emergency';
  const ALERT     = 'alert';
  const CRITICAL  = 'critical';
  const ERROR     = 'error';
  const WARNING   = 'warning';
  const NOTICE    = 'notice';
  const INFO      = 'info';
  const DEBUG     = 'debug';

  public $level = 'warning';

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

  public function __construct($params = array())
  {

  }

  /**
   *@param $exception ErrorException
   *@param $context array
   *@return null
   */
  public function handle(\ErrorException $exception, array $context)
  {
    $levels = array_flip($this->sevirity);
    $sevirities = array_slice($levels,$levels[$this->level]-9809,null,true);

    if(!in_array($this->sevirity[$exception->getSeverity()], $sevirities)  ){

      $context['exception'] = $exception;
      $this->{$this->sevirity[$exception->getSeverity()]}($exception->getMessage(),$context);
    }else{
      return false;
    }

  }
  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function emergency($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function alert($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function critical($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function error($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function warning($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function notice($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function info($message, array $context = array());

  /**
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function debug($message, array $context = array());

  /**
   * @param $level mixed
   * @param $message string
   * @param $context array
   * @return null
   */
  public abstract function log($level, $message, array $context = array());
}

?>