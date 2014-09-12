<?php
namespace Phiber\Logger;
use Phiber\Event\event;
use Phiber\Event\eventfull;

class file extends logger
{

  protected $file = null;

  public function __construct($params = array(),$globalLevel = 'error')
  {
    $logFile = $params[1];
    $this->file = $logFile;
    $this->level = $globalLevel;
    $date = new \DateTime($this->config->PHIBER_TIMEZONE);
    $dateTime = (array) $date;
    $this->prepend = $dateTime['date'].' ['.\Phiber\tools::getIp().'] ['.$_SERVER['REQUEST_URI'].'] ';
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
    $event = new event(self::EVENT_ERROR, __class__);
    $event->level = $level;
    $event->message = $message;
    $event->context = $context;
    eventfull::notify($event);

    $message = '['.$level.'] '.$message;
    $exception = false;
    if(isset($context['exception']) && $context['exception'] instanceof \ErrorException){
      $exception = true;
      $message = $message.PHP_EOL.$context['exception']->getFile().':'.$context['exception']->getLine().PHP_EOL . $context['exception']->getTraceAsString() . PHP_EOL;
    }
    $message = $this->prepend.' '.str_replace('#',PHP_EOL."#",$message).' '.$this->append;


    if($this->level === 'debug'){
      if($exception){
        $object = $context['exception'];
      }else{
        $object = (count($context))?$context:$message;
      }
      \Phiber\tools::wtf($object);
    }
    error_log($message.PHP_EOL,3,$this->file);


  }

}

?>