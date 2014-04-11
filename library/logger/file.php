<?php
namespace Phiber\Logger;

class file extends logger
{

  protected $logs = array();
  protected $file = null;

  public function __construct($params = array())
  {
    $logName = $params[0];
    $logFile = $params[1];
    $this->logs[$logName] = $logFile;
    $this->file = $logFile;
    $this->level = $this->config->logLevel;
  }
  public function addLog($logName, $logFile)
  {
    $this->logs[$logName] = $logFile;
    $this->file = $logFile;
    return $this;
  }
  public function getLog($logName)
  {
    $this->file = $this->logs[$logName];
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
  protected function log($level, $message, array $context = array())
  {
    if(null === $this->file){
      $logs = array_values($this->logs);
      $this->file = $logs[0];
    }
    $message = '['.$level.'] '.$message;
    if(isset($context['exception']) && $context['exception'] instanceof \ErrorException){
      $message = $message.PHP_EOL.$context['exception']->getFile().':'.$context['exception']->getLine().PHP_EOL . $context['exception']->getTraceAsString() . PHP_EOL;
    }
    $message = $this->prepend.' '.str_replace('#',PHP_EOL."#",$message).' '.$this->append;

    error_log($message.PHP_EOL,3,$this->file);
    if($this->level === 'debug'){
      if(isset($context['exception']) && $context['exception'] instanceof \ErrorException){
        $object = $context['exception'];
      }else{
        $object = (count($context))?$context:$message;
      }
      \tools::wtf($object);
    }


  }

}

?>