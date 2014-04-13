<?php
namespace Phiber\Logger;

class file extends logger
{

  protected $file = null;

  public function __construct($params = array(),$globalLevel = 'error')
  {
    $logName = $params[0];
    $logFile = $params[1];
    $this->file = $logFile;
    $this->level = $globalLevel;
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
    $message = '['.$level.'] '.$message;
    $exception = false;
    if(isset($context['exception']) && $context['exception'] instanceof \ErrorException){
      $exception = true;
      $message = $message.PHP_EOL.$context['exception']->getFile().':'.$context['exception']->getLine().PHP_EOL . $context['exception']->getTraceAsString() . PHP_EOL;
    }
    $message = $this->prepend.' '.str_replace('#',PHP_EOL."#",$message).' '.$this->append;

    error_log($message.PHP_EOL,3,$this->file);
    if($this->level === 'debug'){
      if($exception){
        $object = $context['exception'];
      }else{
        $object = (count($context))?$context:$message;
      }
      \tools::wtf($object);
    }


  }

}

?>