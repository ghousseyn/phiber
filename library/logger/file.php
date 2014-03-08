<?php
namespace logger;

class file extends logger
{

  protected $logs = array();
  protected $file = null;

  public function __construct($params)
  {
    $logName = $params[0];
    $logFile = $params[1];
    $this->log[$logName] = $logFile;
    $this->file = $logFile;
  }
  public function addLog($logName, $logFile)
  {
    $this->log[$logName] = $logFile;
    $this->file = $logFile;
    return $this;
  }
  public function getLog($logName)
  {
    $this->file = $this->log[$logName];
    return $this;
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

  /**
   *
   * @param $level mixed
   * @param $message string
   * @param $context array
   * @return null
   */
  public function log($level, $message, array $context = array())
  {
    if(null === $this->file){
      $logs = array_values($this->logs);
      $this->file = $logs[0];
    }
    $message = '['.$level.'] '.$message.PHP_EOL.$context['exception']->getFile().':'.$context['exception']->getLine().PHP_EOL . $context['exception']->getTraceAsString() . PHP_EOL;
    error_log($message,3,$this->file);
  }

}

?>