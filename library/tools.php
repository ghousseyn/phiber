<?php

/**
 * Tools class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */

class tools
{

  public static function getInstance()
  {
    return new self();
  }

  public static function convertTime($size)
  {
    if(0 == $size){
      return;
    }
    $s = array('s', 'min', 'H');
    $e = floor(log($size) / log(60));

    return ($size / pow(60, floor($e))).$s[$e];
  }

  public static function convertSize($size)
  {
    if(0 == $size){
      return;
    }
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
  }

  public static function orDefault($value, $default)
  {
    if(null !== $value){
      return $value;
    }
    return $default;
  }
  /*
   * Thanks to Aaron Fisher http://www.aaron-fisher.com/articles/web/php/wtf/
   */
  public static function wtf($var, $arrayOfObjectsToHide = array(), $fontSize = 12)
  {
    $text = print_r($var, true);
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);

    if($var instanceof \ErrorException){
      $text = $var->getMessage().PHP_EOL;
      $text .= PHP_EOL.'Code: '.$var->getCode().' File: '.$var->getFile().':'.$var->getLine().PHP_EOL;

      $trace = $var->getTrace();
      $first = array_shift($trace);
      $text .= PHP_EOL.'Vars:'.PHP_EOL.PHP_EOL;
      foreach($first['args'][4] as $var => $value){
        if(is_object($value)){

          $value = 'Instance of '.get_class($value);
        }else{
          $value = print_r($value,true);
        }

        $text .= " $$var = $value".PHP_EOL;

      }
      $text .= PHP_EOL.'Trace:'.PHP_EOL;
      foreach($trace as $step => $data){
        $class = '';
        if(isset($data["class"])){
          $class = $data["class"].'->';
        }

       $text .= PHP_EOL.$step.'. '.$class.$data["function"].'() '.$data["file"].':'.$data["line"].PHP_EOL;
       if(isset($data['args']) && count($data['args'])){
         $text .= PHP_EOL.'  Vars:'.PHP_EOL;
         foreach($data['args'] as $arg => $val){
           $text .= print_r($val,true).PHP_EOL;
         }
      }
      unset($class);
      }
      $text = preg_replace('#(Code|File|Trace|Vars):\s#', '<span style="color: #079700;font-weight:bold;">$1:</span>', $text);
      $text = preg_replace('#(Debug|Info|Notice):\s#', '<span style="color: #079700;">$1:</span>', $text);
      $text = preg_replace('#(Warning|Error|Critical|Alert|Emergency):\s#', '<span style="color: red;">$1:</span>', $text);
      // color code object properties
      $pattern = '#\$(\w+)\s=\s(\w+)#';
      $replace = '<span style="color: #000099;">$$1</span> = ';
      $replace .= '<span style="color: #009999;">$2</span>';
      $text = preg_replace($pattern, $replace, $text);

    }else{
      foreach($arrayOfObjectsToHide as $objectName){
        $searchPattern = '#(\W' . $objectName . ' Object\n(\s+)\().*?\n\2\)\n#s';
        $replace = "$1<span style=\"color: #FF9900;\">";
        $replace .= "--&gt; HIDDEN - courtesy of wtf() &lt;--</span>)";
        $text = preg_replace($searchPattern, $replace, $text);
      }
    // color code objects
    $text = preg_replace('#(\w+)(\s+Object\s+\()#s', '<span style="color: #079700;">$1</span>$2', $text);
    // color code object properties
    $pattern = '#\[(\w+)\:(public|private|protected)\]#';
    $replace = '[<span style="color: #000099;">$1</span>:';
    $replace .= '<span style="color: #009999;">$2</span>]';
    $text = preg_replace($pattern, $replace, $text);
    }
    echo '<pre style="font-size: ' . $fontSize . 'px;	line-height: ' . $fontSize . 'px;background-color: #fff; padding: 10px;">' . $text . '</pre>';
  }

}
